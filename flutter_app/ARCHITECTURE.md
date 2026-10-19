# Flutter architecture
FoodHub follows a simple layered structure: `screens` are UI, `viewmodels` hold presentation state, `repositories` provide feature-level data access, and `core/services` handle transport/session concerns. This mirrors the separation-of-concerns approach recommended in Flutter's architecture guidance.

Future feature modules should use:
- `models/` for immutable API models
- `repositories/` for server/data access
- `viewmodels/` for UI state
- `screens/` for views
- `widgets/` for reusable UI
- `core/services/` for API, auth, notifications and device integrations
