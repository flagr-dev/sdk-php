# flagr-sdk (PHP)

PHP SDK for [flagr.dev](https://flagr.dev) — evaluate feature flags with a direct API call. No dependencies beyond ext-curl and ext-json.

## Install

```bash
composer require flagr/sdk
```

Requires PHP 8.1+, ext-curl, ext-json.

## Quick start

```php
use Flagr\FlagrClient;

$flagr = new FlagrClient(sdkKey: $_ENV['FLAGR_ENV_KEY']);

if ($flagr->isEnabled('new-checkout', tenantId: $userId)) {
    // show new checkout
}
```

## Usage

### `isEnabled`

```php
$enabled = $flagr->isEnabled(
    flagKey:  'new-checkout',
    tenantId: $userId,
    default:  false,   // returned if flag is unknown or request fails
);
```

Every call makes a single HTTP POST to `/evaluate`. No local cache, no background connection.

## Flag states

| State | `isEnabled` result |
|---|---|
| `enabled` | `true` for every tenant |
| `disabled` | `false` for every tenant |
| `partially_enabled` | `true` only if the tenant ID is in the enabled list |

## Exceptions

| Exception | When |
|---|---|
| `Flagr\AuthenticationException` | SDK key rejected (HTTP 401) |
| `Flagr\EvaluationException` | Network error or unexpected HTTP status |

## Lifecycle

The `FlagrClient` reuses a single cURL handle across calls — instantiate once and reuse within a request. The handle is closed automatically on destruction.
