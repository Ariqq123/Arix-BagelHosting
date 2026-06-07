# PackSquash Integration Design

**Date:** 2026-06-07
**Status:** Approved

## Summary

Integrate PackSquash (Minecraft Java Edition resource/data pack optimizer) as a core per-server admin tool. Server admins can upload a pack, run optimization using one of four guided presets, host the result on mcpacks.dev, and automatically apply the optimized pack URL + SHA1 to the server's `server.properties` (with overwrite warning).

## Architecture

**Subsystem:** `ServerTools` (built for future extensibility, but v1 ships only PackSquash tool)

**New files:**
- `app/Http/Controllers/Server/Tools/PackSquashController.php`
- `app/Services/Server/Tools/PackSquashService.php`
- `app/Models/ServerToolRun.php`
- `database/migrations/xxxx_create_server_tool_runs_table.php`
- `resources/scripts/components/server/tools/PackSquashTool.tsx` + supporting components

**Hosting:** `McpacksDevHost` (implements `PackHost` interface) — no auth, 250 MB limit, returns `download_url` + `sha1` + ready-made `server_properties`.

**Execution:** Docker container (ephemeral) running PackSquash. Panel spawns container per job with volume mounts for input/output.

**Arix Theme:** All React components follow existing Arix server-page patterns (Tailwind tokens, card layouts, `useTranslation('arix/server/tools')`). User-facing server pages use `templates/base/core` layout (not `layouts.arix`).

## Data Model

**Table:** `server_tool_runs`

Fields: `server_id`, `tool` (`packsquash`), `preset`, `input_filename`, `output_filename`, `host_provider`, `host_uuid`, `download_url`, `sha1`, `original_size`, `optimized_size`, `status`, `error_message`, `meta` (JSON), `started_at`, `completed_at`, timestamps.

Stores both local result metadata and hosted URL/SHA1 for flexible future hosting backends.

## UI Flow (Arix-styled)

**Entry:** Server management page → "PackSquash" section.

**States:**
1. Idle — file dropzone + 4 preset radios (Maximum Compression, Balanced, Fastest, Protection-focused) + Run button
2. Running — progress bar + status + cancel
3. Completed — compression stats + Download / Copy URL / Copy server.properties snippet / **Apply to this server** (warning modal)
4. Failed — friendly error + raw logs + retry

**Apply warning (mandatory):**
> **Warning:** This will overwrite the existing `resource-pack` URL and `resource-pack-sha1` in this server's `server.properties`. The current values will be lost. Are you sure?

**History:** Table of previous runs below the tool (preset, date, ratio, links).

## Error Handling

Docker failures, invalid packs, mcpacks.dev 422 (size limit), timeouts, and network errors are caught, logged, stored on `ServerToolRun`, and shown to the user with actionable recovery (retry, download local fallback, contact admin).

## Testing

Manual verification checklist covers happy path (all 4 presets), invalid input, Docker unavailability, mcpacks.dev errors, and apply action. Automated tests are optional for v1.

## Scope

**v1 includes:**
- PackSquash optimization with 4 presets
- Docker execution
- mcpacks.dev hosting
- Automatic "Apply to server" with overwrite warning
- Arix-themed per-server UI
- History of runs

**Out of v1:**
- Rate limiting, admin cross-server dashboard, additional tools (world optimizer, etc.)

## Future Extensibility

`ServerTools` + `PackHost` interface designed so later tools and alternative hosts (S3, local filesystem, NullHost) plug in with minimal changes. "Apply to server" action demonstrates the pattern for future server-configuration tools.