# Add auth, registration and profile

## Why
Members need to register with phone + password, wait for admin approval, log in, and manage a profile. OTP verification will come later.

## What changes
- Register (new users are role `user`, status `pending`, DPDP consent required), login (phone+password, throttled), logout.
- `account.status` page for pending/rejected/blocked members.
- Profile show/edit/update (avatar, password), language switch + `SetLocale` middleware.
- `PhoneVerifier` interface with a no-op default so an OTP step can be added later.

## Out of scope
OTP, password reset by SMS, sub-admin areas.
