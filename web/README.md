# FRESYS-TCNHS — JavaScript + Firebase Edition

A rewrite of the original PHP/MySQL voting system as a **static JavaScript app** using
the **Firebase Web SDK (Firestore + Auth)**. No PHP, no MySQL, no build step.

## Pages

| File | Purpose |
|---|---|
| `index.html` | Landing page (Student voting / Staff login) |
| `verify.html` | Student verification (code or QR) |
| `ballot.html` | Official ballot + review + submission |
| `results.html` | Live results and winners (staff, or public when enabled) |
| `login.html` / `signup.html` | Staff authentication |
| `dashboard.html` | Admin/Adviser console (overview, candidates, students, accounts, settings, archive) |

## Firestore data model

```
settings/main      { schoolName, electionName, academicYear, repsRule, resultsPublic, votingOpen }
positions/{id}     { name, order, slots }
parties/{id}       { name }
years/{id}         { name }
sections/{id}      { gradeLevel, name }
candidates/{id}    { name, position, party, year, election, photo }
codes/{code}       { studentId, gradeLevel }        // verification index
students/{id}      { firstName, middleName, lastName, gender, gradeLevel,
                     section, code, voteStatus, dateCasted, createdAt }
votes/{studentId}  { studentId, gradeLevel, selections[], createdAt }   // immutable, 1 per student
staff/{uid}        { name, username, role, status, createdAt }
archive/{id}       { electionName, academicYear, stats, results, archivedBy, archivedAt }
```

## One-time Firebase setup

1. **Enable sign-in methods** — Firebase Console → Authentication → Sign-in method:
   - Enable **Email/Password** (staff accounts).
   - Enable **Anonymous** (student voters).
2. **Create Firestore** — Build → Firestore Database → Create database (production mode).
3. **Deploy the rules** — paste `firestore.rules` into Firestore → Rules → Publish,
   or run `firebase deploy --only firestore:rules` from this folder.
4. **Create the first Admin** (required once, because the rules only let an Admin approve staff):
   - Authentication → Users → **Add user**. Email: `<username>@staff.fresys-tcnhs.app`
     (e.g. `admin@staff.fresys-tcnhs.app`), set a password.
   - Firestore → **staff** collection → Add document with **ID = that user's UID** and fields:
     ```
     name: "Your Name"
     username: "admin"
     role: "Admin"
     status: "Approved"
     ```
   - Log in at `login.html` with username `admin` and the password you set.
   - All further Admin/Adviser accounts can then be created via `signup.html` and approved
     from **Dashboard → Accounts**.
5. *(Optional)* Add a `settings/main` document; the app falls back to sensible defaults.

## Running locally

Any static server works (ES modules require http, not `file://`):

```powershell
# from this folder
python -m http.server 5173      # or:  npx serve .
# open http://localhost:5173/
```

## Deploying

**Vercel (static):** import the repo, set **Root Directory** to `web`, framework preset
**Other**, no build command. Add the deployment domain under Firebase Console →
Authentication → Settings → Authorized domains.

**Firebase Hosting (alternative):**

```powershell
npm i -g firebase-tools
firebase login
firebase deploy --only hosting,firestore:rules   # run inside web/
```

## Election integrity

- **One vote per student:** a `votes/{studentId}` document can only be created while that
  student's `voteStatus` is `Available`, and it is immutable (no update/delete by anyone).
- **Final submission:** the ballot writes the vote and flips the student to `Casted` in one
  atomic batch, so a partial ballot can never be recorded.
- **Verification:** codes are only fetchable by exact id (`get`), never listable, and graders
  must match the student's grade.
- **Staff gating:** every management action and results read requires an approved staff profile.

> **Note:** this is a *serverless* design. Because Firestore rules can't fully prevent a
> determined client from marking *another* unvoted student as `Casted` (a denial, not a forged
> vote), production-grade hardening for high-stakes elections should move vote writing and
> counting into a trusted backend (Cloud Functions + Admin SDK) or the Firestore emulator test
> suite. The rules here are already far stricter than the original PHP endpoints, which trusted
> client-supplied student IDs.

## Configuration

Edit `js/firebase-config.js` with your own Firebase web app config. The web `apiKey` is not a
secret — access is enforced entirely by `firestore.rules`.
