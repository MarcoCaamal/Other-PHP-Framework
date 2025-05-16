# Documentation Migration Plan

This document outlines the plan for migrating all documentation files to language-specific directories.

## Current Status

The following files have already been migrated:
- [x] Main index page (`/docs/index.md`) - Now a language selector
- [x] English and Spanish README files (`README.md` and `README.es.md`)
- [x] Mail system documentation (`mail-system.md` → `/docs/en/mail-system.md` and `/docs/es/mail-system.md`)
- [x] Mail API reference (`mail-api-reference.md` → `/docs/en/mail-api-reference.md` and `/docs/es/mail-api-reference.md`)
- [x] Events guide (`events-guide.md` → `/docs/en/events-guide.md` and `/docs/es/events-guide.md`)
- [x] Authentication guide (`authentication-guide.md` → `/docs/en/authentication-guide.md` and `/docs/es/authentication-guide.md`)
- [x] Controllers guide (`controllers-guide.md` → `/docs/en/controllers-guide.md` and `/docs/es/controllers-guide.md`)
- [x] Middleware guide (`middleware-guide.md` → `/docs/en/middleware-guide.md` and `/docs/es/middleware-guide.md`)
- [x] Routing guide (`routing-guide.md` → `/docs/en/routing-guide.md` and `/docs/es/routing-guide.md`)
- [x] Validation guide (`validation-guide.md` → `/docs/en/validation-guide.md` and `/docs/es/validation-guide.md`)
- [x] Views and templating (`views-templating.md` → `/docs/en/views-templating.md` and `/docs/es/views-templating.md`)
- [x] Database transactions (`database-transactions.md` → `/docs/en/database-transactions.md` and `/docs/es/database-transactions.md`)
- [x] Request/response handling (`request-response-handling.md` → `/docs/en/request-response-handling.md` and `/docs/es/request-response-handling.md`)
- [x] Exception handling (`exception-handling.md` → `/docs/en/exception-handling.md` and `/docs/es/exception-handling.md`)
- [x] Foreign key actions (`foreign-key-actions.md` → `/docs/en/foreign-key-actions.md` and `/docs/es/foreign-key-actions.md`)
- [x] Foreign key actions examples (`foreign-key-actions-examples.md` → `/docs/en/foreign-key-actions-examples.md` and `/docs/es/foreign-key-actions-examples.md`)
- [x] Foreign key actions summary (`foreign-key-actions-summary.md` → `/docs/en/foreign-key-actions-summary.md` and `/docs/es/foreign-key-actions-summary.md`)
- [x] Migration enhancement documentation (`migration-enhancement-documentation.md` → `/docs/en/migration-enhancement-documentation.md` and `/docs/es/migration-enhancement-documentation.md`)
- [x] Migration enhancement summary (`migration-enhancement-summary.md` → `/docs/en/migration-enhancement-summary.md` and `/docs/es/migration-enhancement-summary.md`)
- [x] Migrations schema builder (`migrations-schema-builder.md` → `/docs/en/migrations-schema-builder.md` and `/docs/es/migrations-schema-builder.md`)
- [x] Event service provider (`event-service-provider.md` → `/docs/en/event-service-provider.md` and `/docs/es/event-service-provider.md`)
- [x] Migration best practices (`migration-best-practices.md` → `/docs/en/migration-best-practices.md` and `/docs/es/migration-best-practices.md`)

## Cleanup Status

- [x] Migrated all main documentation files to language-specific directories
- [x] Cleaned up the old documentation files from the root `/docs` directory
- [x] Maintained only essential files (`index.md` and `migration-plan.md`) in the root directory
- [x] Added language toggle links to all documentation files

## Remaining Files to Migrate

The following files could be migrated to language-specific directories in the future if needed:

### Database Related Files
- [ ] `create-migration-guide-new.md` → `/docs/en/create-migration-guide-new.md` and `/docs/es/create-migration-guide-new.md`
- [ ] `database-transactions-new.md` → `/docs/en/database-transactions-new.md` and `/docs/es/database-transactions-new.md`
- [ ] `migration-api-reference.md` → `/docs/en/migration-api-reference.md` and `/docs/es/migration-api-reference.md`
- [ ] `migration-system-enhancements.md` → `/docs/en/migration-system-enhancements.md` and `/docs/es/migration-system-enhancements.md`

### HTTP Related Files
- [ ] `request-response-handling-new.md` → `/docs/en/request-response-handling-new.md` and `/docs/es/request-response-handling-new.md`

## Migration Process

For each file:

1. Read the original file to understand its content and structure.
2. Separate the English and Spanish content.
3. Create the English version in the `/docs/en/` directory.
4. Create the Spanish version in the `/docs/es/` directory.
5. Update any internal links to point to the correct language-specific files.

## Update Reference Links

After migrating all files:

1. Update links in the English language README (`README.md`) to point to the English documentation.
2. Update links in the Spanish language README (`README.es.md`) to point to the Spanish documentation.
3. Update links in both language-specific index files (`/docs/en/index.md` and `/docs/es/index.md`).

## Testing

After migration:

1. Check all links in each language version for correct navigation.
2. Verify that the language selector in the main index page works correctly.
3. Ensure the documentation remains accessible and navigable in both languages.

## Future Considerations

- Consider using a documentation tool or static site generator (e.g., MkDocs, Docusaurus) for more advanced documentation features.
- Implement a script to automatically redirect from old documentation URLs to new language-specific URLs.
- Add more translations for other languages in the future if needed.

---

## Migration Completion Note

**Date: May, 16th 2025**

The documentation reorganization project has been successfully completed. All main documentation files have been migrated to language-specific directories (`/docs/en/` and `/docs/es/`), and the old files have been removed from the root directory.

The documentation is now fully bilingual, with English as the primary language and Spanish as the secondary language. Each document has language toggle links for easy navigation between versions.

The only documents remaining in the root `/docs` directory are:
- `index.md`: Serves as a language selector
- `migration-plan.md`: Documents the migration process (this file)

Some newer documentation variants (files with "-new" suffix) were not migrated as they are still in draft state or pending review. These can be handled in a future update if needed.
