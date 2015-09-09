# Workable for SilverStripe
Adds Workable API integration to SilverStripe projects.

## Configuration
First, add your API key using a constant, preferably in your `_ss_environment.php` file.

```php
define('WORKABLE_API_KEY','your_api_key');
```

Alternatively, you can add your API key in the config, although this is not recommended.

```yaml
Workable:
  apiKey: your_api_key
```

Then, just add your subdomain to the config.

```
Workable:
  subdomain: example
```

## Usage

Right now, only one API call is available.

```php
$params = ['state' => 'published'];
Workable::create()->getJobs($params);
```

This returns an `ArrayList`, so you can iterate over it on the template.

```html
<% loop $Jobs %>
$Title, $Url
<% end_loop %>
```

For nested properties, you can use the dot-separated syntax.

```html
<% loop $Jobs %>
$Title ($Location.City)
<% end_loop %>
```

### Property transformation

The Workable API returns is properties in `snake_case`. Simply convert these to `UpperCamelCase` to call them on each result.

```html
$FullTitle, $Url, $ZipCode, $Department, $CreatedAt
```

