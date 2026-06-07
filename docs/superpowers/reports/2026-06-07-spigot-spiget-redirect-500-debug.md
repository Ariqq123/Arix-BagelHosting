# Spigot/Spiget Marketplace Install 500 Debug Report

**Date:** 2026-06-07  
**Slug:** 2026-06-07-spigot-spiget-redirect-500-debug  
**Symptom:** 500 "There was an error while communicating with the machine running this server" when installing plugins via Spigot platform in the Arix marketplace.

## Reproduction
Marketplace → Spigot tab → search → install any non-premium plugin.

## Root Cause
In `PluginMarketplaceService::spigetVersions`, the non-external (standard) Spiget path returns the raw redirect endpoint:

```php
'downloadUrl' => "https://api.spiget.org/v2/resources/{$project}/download",
```

The class-level Guzzle client is created with `'allow_redirects' => false` (line 31). Only the `external` branch calls `resolveDownloadUrl` (which uses per-request redirect following + `on_stats` to capture the final URI). All other platforms (Modrinth, Hangar, CurseForge) return pre-resolved direct download URLs.

When `PluginController::installMarketplace` calls `pullToPlugins` → `DaemonFileRepository::pull`, Wings receives a 302 and fails. This surfaces as `DaemonConnectionException` (unhandled in the controller) → HTTP 500.

## Affected Files
- `app/Services/Plugins/PluginMarketplaceService.php` (spigetVersions, resolveDownloadUrl)
- `app/Http/Controllers/Api/Client/Servers/PluginController.php` (installMarketplace, pullToPlugins)
- `app/Repositories/Wings/DaemonFileRepository.php` (pull)

## Recommended Fix
Apply the one-line change in the non-external map (around line 360):

```php
// before
'downloadUrl' => "https://api.spiget.org/v2/resources/{$project}/download",

// after
'downloadUrl' => $this->resolveDownloadUrl("https://api.spiget.org/v2/resources/{$project}/download") ?? "https://api.spiget.org/v2/resources/{$project}/download",
```

This makes Spiget non-external behavior identical to the external path and other marketplaces. No other changes required.

## Swarm Agents Used
- 4 parallel analysis agents + 1 synthesis agent
- All findings converged on the redirect-resolution gap for Spiget.