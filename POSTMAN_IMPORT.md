# How to Import API Documentation into Postman

This guide explains how to import the Kippis API collection into Postman.

## Method 1: Direct URL Import (Recommended)

1. **Open Postman**
2. Click **Import** button (top left)
3. Select the **Link** tab
4. Enter one of these URLs:
   - **Production**: `https://kippis.raversys.uk/docs.postman`
   - **Local Development**: `http://localhost:8000/docs.postman`
5. Click **Continue**
6. Click **Import**

The collection will be imported with all endpoints, request examples, and documentation.

## Method 2: Download and Import File

### Step 1: Download the Collection

You can download the Postman collection file in several ways:

#### Option A: Via Browser
- Visit: `https://kippis.raversys.uk/docs.postman`
- The JSON file will download automatically
- Save it to your computer (e.g., `kippis-api-collection.json`)

#### Option B: Via Command Line (cURL)
```bash
# Production
curl -o kippis-api-collection.json https://kippis.raversys.uk/docs.postman

# Local Development
curl -o kippis-api-collection.json http://localhost:8000/docs.postman
```

#### Option C: Copy from Server
If you have server access, the file is located at:
```
storage/app/private/scribe/collection.json
```

### Step 2: Import into Postman

1. **Open Postman**
2. Click **Import** button (top left)
3. Select the **File** tab
4. Click **Upload Files** or drag and drop the `kippis-api-collection.json` file
5. Click **Import**

## Method 3: Import from Documentation Page

1. Visit the API documentation: `https://kippis.raversys.uk/docs`
2. Look for the **Postman collection** link in the sidebar (bottom)
3. Click the link to download the collection
4. Import the downloaded file into Postman using Method 2

## What's Included in the Collection

The Postman collection includes:

- ✅ All API endpoints (Customers, Stores, Support Tickets)
- ✅ Request examples with proper headers
- ✅ Environment variables for base URL
- ✅ Request/response documentation
- ✅ Authentication examples (Bearer tokens)
- ✅ Query parameters and body examples

## Setting Up Environment Variables

After importing, you may want to create a Postman environment:

1. Click **Environments** in the left sidebar
2. Click **+** to create a new environment
3. Add these variables:
   - `base_url`: `https://kippis.raversys.uk`
   - `api_url`: `https://kippis.raversys.uk/api`
   - `token`: (your JWT token - leave empty initially)

4. Save the environment
5. Select the environment from the dropdown (top right)

## Updating the Collection

The Postman collection is automatically generated when you run:

```bash
php artisan scribe:generate
```

To get the latest version:
1. Run the command above
2. Re-import the collection using Method 1 or Method 2

## Troubleshooting

### Collection Not Found (404 Error)
- Make sure you've run `php artisan scribe:generate`
- Check that the route is accessible: `https://kippis.raversys.uk/docs.postman`

### Import Fails
- Ensure the file is valid JSON
- Check Postman version (should be v8.0 or later)
- Try downloading the file first, then importing

### Endpoints Show Wrong Base URL
- The collection uses the base URL from `config/scribe.php`
- Regenerate the collection after changing the config

## Quick Links

- **API Documentation**: https://kippis.raversys.uk/docs
- **Postman Collection**: https://kippis.raversys.uk/docs.postman
- **OpenAPI Spec**: https://kippis.raversys.uk/docs.openapi

---

**Note**: The collection is generated automatically and includes all documented API endpoints. Make sure to regenerate it after adding new endpoints or updating existing ones.

