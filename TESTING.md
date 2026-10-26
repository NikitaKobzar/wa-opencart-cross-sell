# Функціональна перевірка 1.0.0

2026-10-26. Лише синтетичні локальні дані. 34 успішних перевірок поспіль.

Середовище: OpenCart 3.0.3.9 / PHP 8.2 / MySQL 8.0.

- Native ZIP installer accepts standalone archive
- Module can be installed in standard module manager
- Ukrainian settings form renders
- New instance saves through authenticated form
- Settings persist after reopening
- Standard Product layout displays ordered cards
- Unrelated product has no cross-sell block
- Images load at configured dimensions
- Product links navigate to correct detail
- Active special and base price are shown
- Duplicate and self IDs are skipped
- Disabled, future, other-store and missing products are skipped
- Unavailable stock hidden when checkout disallows it
- Unavailable stock shown when store permits backorders
- Limit applied after eligibility filtering
- Empty eligible set produces no empty heading
- Disabled module disappears without deleting settings
- Guest price restriction hides both base and special
- Currency follows native currency selection
- HTML in merchant title is rendered as text
- Invalid IDs rejected without modifying saved settings
- Out-of-range limit rejected
- Editing unrelated module is rejected
- Unauthenticated request cannot save
- Invalid user token cannot save
- Mobile cards fit viewport and links remain usable
- Keyboard focus reaches product links
- Desktop storefront and admin render without PHP errors
- Actual settings form submits and keeps module identity
- Read-only administrator cannot modify module
- Changed source product moves the block to its page
- Image size changes regenerate requested dimensions
- Uninstall removes own settings without changing products
- Reinstall and reconfigure restore functionality

Межі перевірки описані в README.md. Архів не містить даних клієнтів або ключів.
