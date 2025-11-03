# Internet Search Tool - Documentation

The SearchEngineTool has been updated to perform **real internet searches** using the SerpAPI service instead of searching the local database.

## Overview

This tool allows users to search the internet for current information, news, documentation, or any web content directly through the chat interface.

## Configuration

### 1. Environment Variables

Add these to your `.env` file:

```env
SERP_API_KEY=your-serpapi-key-here
SERP_ENGINE=google
```

### 2. Get SerpAPI Key

1. Go to [SerpAPI](https://serpapi.com/)
2. Sign up for a free account (100 free searches/month)
3. Copy your API key
4. Add it to `.env`

### 3. Supported Search Engines

You can change the search engine by updating `SERP_ENGINE`:

- `google` (default) - Google Search
- `bing` - Bing Search
- `yahoo` - Yahoo Search
- `duckduckgo` - DuckDuckGo
- `yandex` - Yandex
- `baidu` - Baidu (Chinese)

## Features

### ✨ Automatic Query Extraction

The tool intelligently extracts search queries from natural language:

```
"Search for Laravel documentation" → "Laravel documentation"
"Find information about AI" → "AI"
"What is quantum computing" → "quantum computing"
"Google best practices for REST APIs" → "best practices for REST APIs"
```

### 🎯 Featured Snippets

When available, the tool highlights:
- ⭐ Featured snippets (Google's answer boxes)
- Knowledge graph results
- Top organic results

### 📊 Formatted Results

Returns up to 10 results with:
- Title
- URL
- Snippet (description)
- Position in search results

## Usage Examples

### Via Chat Command

```bash
# Search for information
php artisan chat "Search for Laravel 12 features" --user=2 --workspace=3

# Find current news
php artisan chat "What's happening with AI today" --user=2 --workspace=3

# Look up documentation
php artisan chat "Find PHP 8.3 documentation" --user=2 --workspace=3

# Research topics
php artisan chat "How to implement JWT authentication" --user=2 --workspace=3
```

### Via API

```bash
POST /api/workspaces/{workspace}/chat
{
  "message": "Search for best Laravel packages 2025"
}
```

### Interactive Mode

```bash
$ php artisan chat --interactive --user=2 --workspace=3

💬 You: Search for machine learning trends

🔍 Search Results:
   Found 10 results for 'machine learning trends':

   1. Top Machine Learning Trends 2025 ⭐
      https://example.com/ml-trends
      Discover the latest trends in machine learning including...

   2. AI and ML: What's Next
      https://example.com/whats-next
      The future of artificial intelligence and machine learning...

   ...and 8 more results.
```

## Response Format

```php
[
    'type' => 'search',
    'query' => 'Laravel documentation',
    'engine' => 'google',
    'results' => [
        [
            'title' => 'Laravel - The PHP Framework',
            'link' => 'https://laravel.com',
            'snippet' => 'Laravel is a web application framework...',
            'displayed_link' => 'laravel.com',
            'position' => 1,
            'featured' => false  // true for answer boxes
        ],
        // ... more results
    ],
    'count' => 10,
    'message' => 'Found 10 results for \'Laravel documentation\'...'
]
```

## Intent Detection

The MultiAgentRouterNode automatically routes to this tool when it detects search keywords:

```php
// Triggers SearchEngineTool:
"search for"
"look for"
"find information"
"documentation"
"google"
"what is"
"who is"
"where is"
"how to"
```

## Configuration Options

In `config/neuron.php`:

```php
'tools' => [
    'search' => [
        'max_results' => 10  // Maximum results to return
    ]
]
```

## Error Handling

The tool gracefully handles errors:

### Missing API Key
```
Error: SERP API key not configured. Please set SERP_API_KEY in your .env file.
```

### API Request Failed
```
Error: SERP API request failed: 401
```

### No Results
```
No results found for 'your search query'.
```

## Rate Limits

**SerpAPI Free Tier:**
- 100 searches/month
- Upgrade for more searches

**Best Practices:**
- Use specific search queries
- Cache common searches if needed
- Monitor usage in SerpAPI dashboard

## Advanced Features

### Featured Snippets

When Google returns an answer box:
```
1. What is Laravel ⭐
   https://laravel.com
   Laravel is a web application framework with expressive, elegant syntax...
```

The ⭐ indicates a featured snippet.

### Knowledge Graph

For entity searches (people, places, things):
```
1. Laravel Framework ⭐
   https://laravel.com
   Laravel is an open-source PHP framework created by Taylor Otwell...
```

## Troubleshooting

### API Key Issues

**Problem:** "SERP API key not configured"

**Solution:**
```bash
# Check .env has the key
grep SERP_API_KEY .env

# If missing, add it
echo "SERP_API_KEY=your-key-here" >> .env

# Clear config cache
php artisan config:clear
```

### Timeout Errors

**Problem:** Searches timing out

**Solution:**
- The tool has a 30-second timeout
- Check your internet connection
- Verify SerpAPI is accessible

### Wrong Engine

**Problem:** Want to use different search engine

**Solution:**
```bash
# Update .env
SERP_ENGINE=bing

# Clear config cache
php artisan config:clear
```

## Testing

### Manual Test

```bash
php artisan chat "Search for Laravel" --user=2 --workspace=3
```

### Check API Key

```bash
php artisan tinker
>>> config('services.serp.api_key')
=> "your-api-key"
```

### Verify Response

```bash
php artisan chat "What is PHP" --user=2 --workspace=3 --verbose
```

## Comparison: Old vs New

| Feature | Old (Database Search) | New (Internet Search) |
|---------|----------------------|----------------------|
| Search Scope | Workspace data only | Entire internet |
| Data Source | Local database | SerpAPI / Google |
| Results | Documents, expenses, users | Web pages, articles, docs |
| Real-time | No | Yes - current info |
| Requires | Database tables | SERP API key |
| Use Case | Internal workspace search | External information |

## Migration Notes

The old database search functionality has been **completely replaced**. If you need to search workspace data:

- Use the **DatabaseQueryTool** for database searches
- Use **SearchEngineTool** for internet searches

## Cost Considerations

- **Free Tier:** 100 searches/month
- **Paid Plans:** Starting at $50/month for 5,000 searches
- **Monitor Usage:** Check SerpAPI dashboard

## Best Practices

1. **Be Specific:** "Laravel 12 new features" vs "Laravel"
2. **Use Quotes:** For exact phrases: `"exact phrase"`
3. **Combine Tools:** Use search for research, database for internal data
4. **Cache Results:** If searching same thing repeatedly
5. **Monitor Limits:** Track SerpAPI usage

## Future Enhancements

Potential improvements:
- [ ] Image search support
- [ ] News search
- [ ] Video search (YouTube)
- [ ] Shopping results
- [ ] Local business search
- [ ] Related searches
- [ ] Search filters (date, region)
- [ ] Result caching
- [ ] Multiple page results

## Support

For issues:
1. Check SerpAPI dashboard for usage/errors
2. Verify API key is correct
3. Test with simple query: "test"
4. Check logs: `storage/logs/laravel.log`

## Related Documentation

- [Chat Command Usage](chat-command-usage.md)
- [MVP Documentation](mvp.md)
- [MultiAgentRouterNode](../../app/Neuron/Nodes/MultiAgentRouterNode.php)
- [SerpAPI Documentation](https://serpapi.com/docs)
