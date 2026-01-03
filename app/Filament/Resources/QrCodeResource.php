<?php

namespace App\Filament\Resources;

use App\Core\Models\QrCode;
use App\Filament\Resources\QrCodeResource\Pages;
use App\Filament\Resources\QrCodeResource\RelationManagers;
use App\Services\QrCodeGeneratorService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrCodeResource extends Resource
{
    protected static ?string $model = QrCode::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-qr-code';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.customer_management');
    }

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('navigation.qr_codes');
    }

    public static function getModelLabel(): string
    {
        return __('system.qr_code');
    }

    public static function getPluralModelLabel(): string
    {
        return __('system.qr_codes');
    }

    public static function canViewAny(): bool
    {
        return Gate::forUser(auth()->guard('admin')->user())->allows('manage_qr_codes');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('QR Code Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->default(fn () => 'QR-' . strtoupper(Str::random(8)))
                            ->helperText('Unique code string that will be embedded in the QR code'),
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\TextInput::make('points_awarded')
                            ->label('Points Awarded')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(10)
                            ->helperText('Points awarded when this QR code is redeemed'),
                    ])
                    ->columns(2),
                Components\Section::make('Status & Availability')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Enable or disable this QR code'),
                        Forms\Components\DateTimePicker::make('start_at')
                            ->label('Start At')
                            ->nullable()
                            ->helperText('When QR code becomes valid (leave empty for immediate activation)'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->nullable()
                            ->helperText('When QR code expires (leave empty for no expiration)')
                            ->after('start_at'),
                    ])
                    ->columns(3),
                Components\Section::make('Usage Limits')
                    ->schema([
                        Forms\Components\TextInput::make('per_customer_limit')
                            ->label('Per Customer Limit')
                            ->numeric()
                            ->minValue(1)
                            ->nullable()
                            ->helperText('Maximum times one customer can use this (leave empty for unlimited)'),
                        Forms\Components\TextInput::make('total_limit')
                            ->label('Total Limit')
                            ->numeric()
                            ->minValue(1)
                            ->nullable()
                            ->helperText('Maximum total redemptions across all customers (leave empty for unlimited)'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('points_awarded')
                    ->label('Points')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_used_count')
                    ->label('Total Uses')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->total_limit 
                            ? "{$state} / {$record->total_limit}" 
                            : (string) $state
                    ),
                Tables\Columns\TextColumn::make('per_customer_limit')
                    ->label('Per Customer')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? (string) $state : 'Unlimited')
                    ->placeholder('Unlimited'),
                Tables\Columns\TextColumn::make('start_at')
                    ->label('Start At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Immediate'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ]),
                Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(fn ($query) => $query->expired()),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming')
                    ->query(fn ($query) => $query->where('start_at', '>', now())),
                Tables\Filters\Filter::make('has_remaining')
                    ->label('Has Remaining Uses')
                    ->query(function ($query) {
                        return $query->where(function ($q) {
                            $q->whereNull('total_limit')
                              ->orWhereColumn('total_used_count', '<', 'total_limit');
                        });
                    }),
                Tables\Filters\Filter::make('fully_used')
                    ->label('Fully Used')
                    ->query(function ($query) {
                        return $query->whereNotNull('total_limit')
                            ->whereColumn('total_used_count', '>=', 'total_limit');
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\Action::make('generate_qr')
                    ->label('Generate QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->action(function (QrCode $record, QrCodeGeneratorService $qrService) {
                        $path = $qrService->generate($record->code);
                        $url = Storage::disk('public')->url($path);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('QR Code Generated')
                            ->body('QR code has been generated successfully.')
                            ->success()
                            ->send();
                        
                        return redirect($url);
                    }),
                Actions\Action::make('download_qr')
                    ->label('Download QR Code')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (QrCode $record, QrCodeGeneratorService $qrService) {
                        return $qrService->download($record->code);
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQrCodes::route('/'),
            'create' => Pages\CreateQrCode::route('/create'),
            'edit' => Pages\EditQrCode::route('/{record}/edit'),
            'view' => Pages\ViewQrCode::route('/{record}'),
        ];
    }
}

