# Add api_keys.php to .gitignore before committing

# ExploreCapitals

ExploreCapitals is a web application that provides information about countries and their capitals around the world.

## AI Country Descriptions

The application generates rich, educational descriptions of countries using the OpenAI API and Wikipedia data. To enable this feature:

1. Get an API key from [OpenAI](https://platform.openai.com/)
2. Add your API key to the `api_keys.php` file:
   ```php
   $openai_api_key = 'your_api_key_here'; // Replace with your actual key
   ```

If no API key is provided, the application will use Wikipedia data as a fallback to generate simpler descriptions.

## Configuration Files

Important: The following files contain sensitive information and should never be committed to version control:
- `api_keys.php` - Contains API keys for external services
- `config.php` - Contains database credentials

These files are already listed in the `.gitignore` file.

## Features

- Information about countries, territories, and dependencies
- Capitals and their details
- AI-generated descriptions with historical and cultural information
- Maps and geographical data
