# FRESYS-TCNHS

Online student election (SSLG) system for **Tangub City National High School**.

## Two versions in this repository

| Folder | Stack | Notes |
|---|---|---|
| `web/` | **JavaScript + Firebase** (Firestore + Auth) | The current rewrite. Static app — deploy on Vercel or Firebase Hosting. See [`web/README.md`](web/README.md). |
| *(root)* | Legacy PHP + MySQL | Original implementation, kept for reference. Requires PHP + MySQL. |

## Quick start (JavaScript version)

1. Read the one-time Firebase setup in [`web/README.md`](web/README.md).
2. Serve the `web/` folder with any static server and open `index.html`.
3. Students verify their code and vote; staff log in to manage the election and view results.

## Security

- Never commit secrets. `includes/serviceAccountKey.json` (a Firebase Admin private key) is
  git-ignored — rotate it if it was ever exposed.
- The web `apiKey` in `web/js/firebase-config.js` is public by design; Firestore Security Rules
  (`web/firestore.rules`) enforce all access control.
