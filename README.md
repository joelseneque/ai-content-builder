# AI Content Builder for Statamic

A Statamic addon that adds an AI-powered content generation button to the Bard editor. Uses OpenAI's API to generate content based on user prompts.

## Features

- Custom Bard toolbar button with sparkles icon
- Configurable system prompts per Bard field
- User-facing instructions displayed in the modal
- Adjustable word count limits
- Markdown to HTML conversion for rich content
- Australian English output by default

## Requirements

- Statamic 5.x
- PHP 8.2+
- OpenAI API key

## Installation

### Via Composer (recommended)

Add the repository to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/joelseneque/ai-content-builder.git"
        }
    ]
}
```

Then require the package:

```bash
composer require joelseneque/ai-content-builder
```

### Manual Installation

1. Copy the addon to `addons/joelseneque/ai-content-builder`
2. Add to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "addons/joelseneque/ai-content-builder"
        }
    ],
    "require": {
        "pqd/ai-content-builder": "*"
    }
}
```

3. Run `composer update`

## Configuration

Add your OpenAI API key to your `.env` file:

```env
OPENAI_API_KEY=your-api-key-here
OPENAI_MODEL=gpt-4
```

## Usage

### Bard Field Configuration

When editing a Bard field in the blueprint, you'll see three new configuration options:

- **AI System Prompt**: The system prompt that defines how the AI should generate content for this field
- **AI User Instructions**: Instructions shown to users in the modal when using the AI Content Builder
- **Max AI Word Response Limit**: Maximum words users can request (0-1000, default 250)

### Using the Button

1. Click the sparkles icon in the Bard toolbar
2. Read the instructions (if configured)
3. Enter your content request
4. Adjust the word count if needed
5. Click "Generate Content" or press Cmd+Enter (Mac) / Ctrl+Enter (Windows)

The generated content will be inserted into the editor as formatted HTML.

## Publishing Config

To publish the config file:

```bash
php artisan vendor:publish --tag=ai-content-builder-config
```

## License

MIT License. See [LICENSE](LICENSE) for details.
