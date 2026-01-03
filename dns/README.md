# Cloudflare Dynamic DNS Updater

A PHP script that automatically updates Cloudflare DNS A records with your current external IP address. Perfect for home servers or dynamic IP scenarios.

## Overview

This script fetches your current external IP address and updates specified Cloudflare DNS A records to point to that IP. It's useful for maintaining DNS records when your IP address changes dynamically.

## Features

- Automatically detects your current external IP address
- Updates multiple Cloudflare zones and DNS records
- Only updates records when IP has changed
- Provides clear console output of actions taken
- Uses Cloudflare API v4

## Requirements

- PHP 7.4 or higher
- Composer
- Cloudflare account with API token
- Internet connection

## Installation

1. Clone this repository:
```bash
git clone <repository-url>
cd dns
```

2. Install dependencies:
```bash
composer install
```

3. Create your configuration file:
```bash
cp cloudflare.json.tpl cloudflare.json
```

4. Edit `cloudflare.json` with your Cloudflare credentials:
```json
{
    "token": "your-cloudflare-api-token",
    "zones": [
        "example.com",
        "subdomain.example.com"
    ]
}
```

## Configuration

### Obtaining a Cloudflare API Token

1. Log in to your Cloudflare account
2. Go to "My Profile" > "API Tokens"
3. Create a new token with the following permissions:
   - Zone - DNS - Edit
   - Zone - Zone - Read
4. Copy the token to your `cloudflare.json` file

### Configuration File Format

The `cloudflare.json` file contains:
- `token`: Your Cloudflare API token
- `zones`: Array of domain names to update (must be DNS A records)

## Usage

Run the script manually:
```bash
php dynamic-ip.php
```

### Automated Updates

For automatic updates, add a cron job:

```bash
# Update every 5 minutes
*/5 * * * * /usr/bin/php /path/to/dns/dynamic-ip.php >> /var/log/cloudflare-dns.log 2>&1

# Update every hour
0 * * * * /usr/bin/php /path/to/dns/dynamic-ip.php >> /var/log/cloudflare-dns.log 2>&1
```

## Output Examples

When IP addresses match:
```
IP MATCH: example.com IP of 203.0.113.42 matches 203.0.113.42.
```

When an update occurs:
```
IP UPDATE: example.com IP updated from 203.0.113.42 to 198.51.100.10.
```

## How It Works

1. Fetches your current external IP using [ipify API](https://www.ipify.org/)
2. Retrieves all zones from your Cloudflare account
3. For each configured zone:
   - Fetches all DNS records
   - Finds A records matching your zone list
   - Compares current IP with DNS record IP
   - Updates the record if IPs don't match

## Security Notes

- The `cloudflare.json` file contains sensitive API credentials and is excluded from version control
- Never commit your `cloudflare.json` file to a repository
- Use API tokens with minimal required permissions
- Rotate your API tokens regularly

## Dependencies

- [Guzzle HTTP](https://github.com/guzzle/guzzle) - PHP HTTP client

## License

This project is provided as-is for personal use.

## Troubleshooting

### "Invalid JSON file" error
Check that your `cloudflare.json` file is valid JSON and properly formatted.

### "Unknown error occurred"
Verify that:
- Your API token has the correct permissions
- The zone names in your configuration exactly match your Cloudflare zones
- The DNS records exist and are A records

### IP not updating
Ensure that:
- The domain names in the `zones` array exactly match your DNS record names
- The DNS records are type A (not AAAA, CNAME, etc.)
- Your API token has write permissions for DNS records
