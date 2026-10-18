// ---------------------------------------------------------------------------
// FRESYS data layer (Firestore)
// ---------------------------------------------------------------------------
// Collections
//   settings/main        { schoolName, electionName, academicYear, repsRule,
//                          resultsPublic, votingOpen }
//   positions/{id}       { name, order, slots }
//   parties/{id}         { name }
//   years/{id}           { name }
//   sections/{id}        { gradeLevel, name }
//   candidates/{id}      { name, position, party, year, election, photo, order }
//   codes/{code}         { studentId, gradeLevel }            (verification index)
//   students/{id}        { firstName, middleName, lastName, gender, gradeLevel,
//                          section, code, voteStatus, dateCasted, createdAt }
//   votes/{studentId}    { studentId, gradeLevel, selections[], createdAt }  (immutable)
//   staff/{uid}          { name, username, role, status, createdAt }
//   archive/{id}         { electionName, academicYear, snapshot[], archivedBy, archivedAt }
// ---------------------------------------------------------------------------
import {
  db, auth,
  collection, doc, getDoc, getDocs, setDoc, addDoc, updateDoc, deleteDoc,
  query, where, orderBy, limit, onSnapshot, writeBatch, serverTimestamp, deleteField, updatePassword
} from "./firebase.js";

export const COLLECTIONS = {
  settings: "settings",
  positions: "positions",
  parties: "parties",
  years: "years",
  sections: "sections",
  candidates: "candidates",
  codes: "codes",
  students: "students",
  votes: "votes",
  staff: "staff",
  archive: "archive",
  loginlog: "loginlog"
};

export const GRADES = ["Grade 7", "Grade 8", "Grade 9", "Grade 10", "Grade 11", "Grade 12"];

// Executive positions every voter selects (1 slot each).
export const EXEC_POSITIONS = [
  "President", "Vice-President", "Secretary", "Treasurer", "Auditor"
];
// Grade-level (JHS/SHS) positions.
export const JHS_POSITIONS = ["P.I.O(JHS)", "Protocol Officer(JHS)"];
export const SHS_POSITIONS = ["P.I.O(SHS)", "Protocol Officer(SHS)"];
// Representative positions (2 slots each).
export const REP_POSITIONS = GRADES.map((g) => `${g} Representative`);

export const ALL_POSITIONS = [...EXEC_POSITIONS, ...JHS_POSITIONS, ...SHS_POSITIONS, ...REP_POSITIONS];

export const POSITION_ORDER = (pos) => {
  const i = ALL_POSITIONS.indexOf(pos);
  return i === -1 ? 999 : i;
};

export const DEFAULT_SETTINGS = {
  schoolName: "TANGUB CITY NATIONAL HIGH SCHOOL",
  electionName: "SUPREME STUDENT LEARNER GOVERNMENT ELECTION",
  academicYear: "2026-2027",
  repsRule: "next",         // original flow: a grade votes the NEXT grade's reps (Grade 12 votes none)
  resultsPublic: false,
  votingOpen: true
};

// Position scopes: "all" (every grade), "jhs" (Grade 7-9), "shs" (Grade 10-12),
// "rep" (grade representative, 2 slots).
export const POSITION_SCOPES = [
  { value: "all", label: "All grades (executive)" },
  { value: "jhs", label: "Junior High (Grade 7-9)" },
  { value: "shs", label: "Senior High (Grade 10-12)" },
  { value: "rep", label: "Grade representative (2 slots)" }
];

export const DEFAULT_POSITIONS = [
  ...EXEC_POSITIONS.map((name) => ({ name, scope: "all", slots: 1 })),
  ...JHS_POSITIONS.map((name) => ({ name, scope: "jhs", slots: 1 })),
  ...SHS_POSITIONS.map((name) => ({ name, scope: "shs", slots: 1 })),
  ...REP_POSITIONS.map((name) => ({ name, scope: "rep", slots: 2 }))
].map((p, i) => ({ ...p, order: i + 1 }));

export const scopeForPosition = (name) => {
  const d = DEFAULT_POSITIONS.find((p) => p.name === name);
  return d ? d.scope : (REP_POSITIONS.includes(name) ? "rep" : "all");
};


// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
export function normalizeName(s) {
  return (s || "").toString().trim();
}

export function fullName(stu, order = "last") {
  if (!stu) return "";
  const { firstName = "", middleName = "", lastName = "" } = stu;
  const mi = middleName ? ` ${middleName.trim()[0] || ""}.` : "";
  return order === "first"
    ? `${firstName} ${lastName}${mi}`.trim()
    : `${lastName}, ${firstName}${mi}`.trim();
}

export function dataUrlToParts(dataUrl) {
  const m = /^data:(.*?);base64,(.*)$/.exec(dataUrl || "");
  return m ? { contentType: m[1], base64: m[2] } : null;
}

// ---------------------------------------------------------------------------
// Settings
// ---------------------------------------------------------------------------
export async function getSettings() {
  const snap = await getDoc(doc(db, COLLECTIONS.settings, "main"));
  return snap.exists() ? { ...DEFAULT_SETTINGS, ...snap.data() } : { ...DEFAULT_SETTINGS };
}

export async function saveSettings(patch) {
  await setDoc(doc(db, COLLECTIONS.settings, "main"), patch, { merge: true });
}

export function watchSettings(cb) {
  return onSnapshot(doc(db, COLLECTIONS.settings, "main"), (s) => {
    cb(s.exists() ? { ...DEFAULT_SETTINGS, ...s.data() } : { ...DEFAULT_SETTINGS });
  });
}

// ---------------------------------------------------------------------------
// Lookup tables (positions, parties, years, sections)
// ---------------------------------------------------------------------------
async function listCollection(name, orderField) {
  const ref = collection(db, name);
  const q = orderField ? query(ref, orderBy(orderField)) : ref;
  const snap = await getDocs(q);
  return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
}

export const listPositions = () => listCollection(COLLECTIONS.positions, "order");
export const listParties = () => listCollection(COLLECTIONS.parties, "name");
export const listYears = () => listCollection(COLLECTIONS.years, "name");
export const listSections = () => listCollection(COLLECTIONS.sections, "name");

export async function addLookup(name, payload) {
  const ref = await addDoc(collection(db, COLLECTIONS[name]), payload);
  return ref.id;
}

export async function updateLookup(name, id, payload) {
  await updateDoc(doc(db, COLLECTIONS[name], id), payload);
}

export async function deleteLookup(name, id) {
  await deleteDoc(doc(db, COLLECTIONS[name], id));
}

// ---------------------------------------------------------------------------
// Positions (configurable). Falls back to the SSLG defaults when unseeded.
// ---------------------------------------------------------------------------
export async function getPositions() {
  let items = [];
  try { items = await listPositions(); } catch (_) { items = []; }
  if (!items.length) {
    return DEFAULT_POSITIONS.map((p, i) => ({ id: `default-${i}`, ...p }));
  }
  return items.map((p, i) => ({ ...p, scope: p.scope || scopeForPosition(p.name), slots: p.slots || 1, order: p.order || i + 1 }));
}

export async function addPosition({ name, scope, slots }) {
  const existing = await getPositions();
  const order = existing.length ? Math.max(...existing.map((p) => p.order || 0)) + 1 : 1;
  return addLookup("positions", { name, scope: scope || "all", slots: Number(slots) || 1, order });
}
export async function updatePosition(id, patch) { return updateLookup("positions", id, patch); }
export async function deletePosition(id) { return deleteLookup("positions", id); }

// Grade applicability for a position (used by the ballot).
export function positionAppliesToGrade(scope, gradeLevel, positionName, repsRule) {
  const idx = GRADES.indexOf(gradeLevel);
  const isJHS = idx >= 0 && idx <= 2;
  if (scope === "all") return true;
  if (scope === "jhs") return isJHS;
  if (scope === "shs") return !isJHS;
  if (scope === "rep") {
    const target = repsRule === "next" ? GRADES[idx + 1] : gradeLevel;
    return positionName === `${target} Representative`;
  }
  return true;
}

// ---------------------------------------------------------------------------
// One-time seeding / bootstrap of configuration collections
// ---------------------------------------------------------------------------
export async function ensureSeed() {
  const created = [];
  const setSnap = await getDoc(doc(db, COLLECTIONS.settings, "main"));
  if (!setSnap.exists()) { await setDoc(doc(db, COLLECTIONS.settings, "main"), { ...DEFAULT_SETTINGS }); created.push("settings"); }

  const positions = await listPositions().catch(() => []);
  if (!positions.length) {
    for (const p of DEFAULT_POSITIONS) await addLookup("positions", p);
    created.push("positions");
  }
  const years = await listYears().catch(() => []);
  if (!years.length) { await addLookup("years", { name: DEFAULT_SETTINGS.academicYear }); created.push("years"); }
  return created;
}

// Duplicate verification-code guard (legacy check_duplicate_codes)
export async function isCodeTaken(code, exceptStudentId) {
  const key = String(code || "").trim();
  if (!key) return false;
  const snap = await getDoc(doc(db, COLLECTIONS.codes, key));
  if (!snap.exists()) return false;
  return snap.data().studentId !== exceptStudentId;
}

// ---------------------------------------------------------------------------
// Public-safe stats: counts only what anonymous visitors may read (votes).
// ---------------------------------------------------------------------------
export async function getPublicStats() {
  const votes = await listVotes();
  return { casted: votes.length };
}

// ---------------------------------------------------------------------------
// Login audit trail (legacy userloginaccount)
// ---------------------------------------------------------------------------
export async function logLogin({ username, name, role, action }) {
  try {
    await addDoc(collection(db, COLLECTIONS.loginlog), {
      username: String(username || "").trim().toLowerCase(),
      name: name || "", role: role || "", action: action || "login",
      userAgent: (typeof navigator !== "undefined" && navigator.userAgent) || "",
      at: serverTimestamp()
    });
  } catch (_) { /* audit logging must never block login */ }
}

export async function listLoginLog() {
  const snap = await getDocs(collection(db, COLLECTIONS.loginlog));
  return snap.docs.map((d) => ({ id: d.id, ...d.data() }))
    .sort((a, b) => (b.at?.seconds || 0) - (a.at?.seconds || 0));
}

// ---------------------------------------------------------------------------
// Change the signed-in staff member's own password.
// ---------------------------------------------------------------------------
export async function changeOwnPassword(newPassword) {
  if (!auth.currentUser) throw new Error("You must be signed in.");
  if ((newPassword || "").length < 6) throw new Error("Password must be at least 6 characters.");
  await updatePassword(auth.currentUser, newPassword);
}


// ---------------------------------------------------------------------------
// Candidates
// ---------------------------------------------------------------------------
export async function listCandidates() {
  const snap = await getDocs(collection(db, COLLECTIONS.candidates));
  return snap.docs
    .map((d) => ({ id: d.id, ...d.data() }))
    .sort((a, b) => POSITION_ORDER(a.position) - POSITION_ORDER(b.position) ||
      (a.name || "").localeCompare(b.name || ""));
}

export function watchCandidates(cb) {
  return onSnapshot(collection(db, COLLECTIONS.candidates), (snap) => {
    cb(snap.docs
      .map((d) => ({ id: d.id, ...d.data() }))
      .sort((a, b) => POSITION_ORDER(a.position) - POSITION_ORDER(b.position) ||
        (a.name || "").localeCompare(b.name || "")));
  });
}

export async function addCandidate(payload) {
  const ref = await addDoc(collection(db, COLLECTIONS.candidates), {
    ...payload, createdAt: serverTimestamp()
  });
  return ref.id;
}

export async function updateCandidate(id, payload) {
  await updateDoc(doc(db, COLLECTIONS.candidates, id), payload);
}

export async function deleteCandidate(id) {
  await deleteDoc(doc(db, COLLECTIONS.candidates, id));
}

// ---------------------------------------------------------------------------
// Students + verification codes
// ---------------------------------------------------------------------------
export async function listStudents() {
  const snap = await getDocs(collection(db, COLLECTIONS.students));
  return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
}

export async function addStudent(stu) {
  const ref = await addDoc(collection(db, COLLECTIONS.students), {
    ...stu, voteStatus: stu.voteStatus || "Available", createdAt: serverTimestamp()
  });
  if (stu.code) {
    await setDoc(doc(db, COLLECTIONS.codes, String(stu.code).trim()), {
      studentId: ref.id, gradeLevel: stu.gradeLevel
    });
  }
  return ref.id;
}

export async function updateStudent(id, patch) {
  const current = await getDoc(doc(db, COLLECTIONS.students, id));
  const prevCode = current.exists() ? current.data().code : null;
  await updateDoc(doc(db, COLLECTIONS.students, id), patch);
  if (patch.code && patch.code !== prevCode) {
    if (prevCode) await deleteDoc(doc(db, COLLECTIONS.codes, String(prevCode).trim()));
    await setDoc(doc(db, COLLECTIONS.codes, String(patch.code).trim()), {
      studentId: id, gradeLevel: patch.gradeLevel || (current.data() && current.data().gradeLevel)
    });
  }
}

export async function deleteStudent(id) {
  const current = await getDoc(doc(db, COLLECTIONS.students, id));
  if (current.exists() && current.data().code) {
    await deleteDoc(doc(db, COLLECTIONS.codes, String(current.data().code).trim()));
  }
  await deleteDoc(doc(db, COLLECTIONS.students, id));
}

// Verify a student by code + grade. Returns { ok, reason, studentId, student }.
export async function verifyStudent(code, gradeLevel) {
  const key = String(code || "").trim();
  if (!key) return { ok: false, reason: "missing" };
  const codeSnap = await getDoc(doc(db, COLLECTIONS.codes, key));
  if (!codeSnap.exists()) return { ok: false, reason: "notfound" };
  const { studentId, gradeLevel: codeGrade } = codeSnap.data();
  if (gradeLevel && codeGrade !== gradeLevel) return { ok: false, reason: "notfound" };
  const studentSnap = await getDoc(doc(db, COLLECTIONS.students, studentId));
  if (!studentSnap.exists()) return { ok: false, reason: "notfound" };
  const student = { id: studentId, ...studentSnap.data() };
  if (student.voteStatus === "Casted") return { ok: false, reason: "alreadycasted", student };
  return { ok: true, studentId, student };
}

// ---------------------------------------------------------------------------
// Ballot composition (which positions a given grade votes for)
// ---------------------------------------------------------------------------
export function ballotPositionsForGrade(gradeLevel, repsRule = "next") {
  const idx = GRADES.indexOf(gradeLevel);
  const isJHS = idx >= 0 && idx <= 2;
  const positions = [...EXEC_POSITIONS, ...(isJHS ? JHS_POSITIONS : SHS_POSITIONS)];
  if (repsRule === "next") {
    const next = GRADES[idx + 1];
    if (next) positions.push(`${next} Representative`);
  } else {
    positions.push(`${gradeLevel} Representative`);
  }
  return positions;
}

// ---------------------------------------------------------------------------
// Voting (one immutable vote document per student)
// ---------------------------------------------------------------------------
// selections: { "President": [{ id, name }], "Grade 8 Representative": [{...},{...}], ... }
export async function submitVotes(studentId, gradeLevel, selections) {
  const flat = [];
  for (const [position, picks] of Object.entries(selections || {})) {
    for (const p of picks || []) {
      if (p == null) continue;
      const candidateId = typeof p === "string" ? p : p.id;
      const candidateName = typeof p === "object" ? (p.name || p.candidateName || "") : "";
      if (candidateId) flat.push({ position, candidateId, candidateName });
    }
  }
  const batch = writeBatch(db);
  batch.set(doc(db, COLLECTIONS.votes, studentId), {
    studentId, gradeLevel, selections: flat, createdAt: serverTimestamp()
  });
  batch.update(doc(db, COLLECTIONS.students, studentId), {
    voteStatus: "Casted", dateCasted: serverTimestamp()
  });
  await batch.commit();
}

export async function hasVoted(studentId) {
  const snap = await getDoc(doc(db, COLLECTIONS.votes, studentId));
  return snap.exists();
}

export async function listVotes() {
  const snap = await getDocs(collection(db, COLLECTIONS.votes));
  return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
}

// ---------------------------------------------------------------------------
// Results computation
// ---------------------------------------------------------------------------
// Returns per-position arrays of { candidateId, name, party, total, byGrade }.
export function computeResults(candidates, votes) {
  const byId = new Map(candidates.map((c) => [c.id, c]));
  const positions = new Map();

  const ensure = (position, candidateId, name) => {
    if (!positions.has(position)) positions.set(position, new Map());
    const rows = positions.get(position);
    if (!rows.has(candidateId)) {
      const c = byId.get(candidateId);
      rows.set(candidateId, {
        candidateId,
        name: name || (c && c.name) || "Unknown",
        party: (c && c.party) || "",
        photo: (c && c.photo) || "",
        total: 0,
        byGrade: Object.fromEntries(GRADES.map((g) => [g, 0]))
      });
    }
    return rows.get(candidateId);
  };

  // Seed every registered candidate so they appear with 0 votes.
  for (const c of candidates) ensure(c.position, c.id, c.name);

  for (const v of votes) {
    const grade = v.gradeLevel;
    for (const sel of v.selections || []) {
      const row = ensure(sel.position, sel.candidateId, sel.candidateName);
      row.total += 1;
      if (grade && row.byGrade[grade] !== undefined) row.byGrade[grade] += 1;
    }
  }

  return [...positions.entries()]
    .map(([position, rows]) => ({
      position,
      candidates: [...rows.values()].sort((a, b) => b.total - a.total || a.name.localeCompare(b.name))
    }))
    .sort((a, b) => POSITION_ORDER(a.position) - POSITION_ORDER(b.position));
}

// Winners: top-1 for exec/JHS/SHS positions, top-2 for representatives.
export function computeWinners(results) {
  const winners = [];
  for (const { position, candidates } of results) {
    const slots = REP_POSITIONS.includes(position) ? 2 : 1;
    const ranked = candidates.filter((c) => c.total > 0).slice(0, slots);
    for (const c of ranked) winners.push({ position, ...c, slots });
  }
  return winners.sort((a, b) => POSITION_ORDER(a.position) - POSITION_ORDER(b.position));
}

// Per-grade winners (legacy gradelevelresult_tablee): for every grade, the
// top candidate(s) of each position based only on that grade's votes.
export function computeGradeWinners(results) {
  const out = [];
  for (const { position, candidates } of results) {
    const slots = REP_POSITIONS.includes(position) ? 2 : 1;
    for (const grade of GRADES) {
      candidates
        .map((c) => ({ position, grade, name: c.name, party: c.party, photo: c.photo, total: c.byGrade[grade] || 0 }))
        .filter((r) => r.total > 0)
        .sort((a, b) => b.total - a.total || a.name.localeCompare(b.name))
        .slice(0, slots)
        .forEach((r) => out.push(r));
    }
  }
  return out.sort((a, b) => GRADES.indexOf(a.grade) - GRADES.indexOf(b.grade) ||
    POSITION_ORDER(a.position) - POSITION_ORDER(b.position));
}

// ---------------------------------------------------------------------------
// Stats
// ---------------------------------------------------------------------------
export async function getOverviewStats() {
  const [students, votes, candidates] = await Promise.all([
    listStudents(), listVotes(), listCandidates()
  ]);
  const byGrade = {};
  for (const g of GRADES) byGrade[g] = { registered: 0, casted: 0 };
  for (const s of students) {
    if (!byGrade[s.gradeLevel]) byGrade[s.gradeLevel] = { registered: 0, casted: 0 };
    byGrade[s.gradeLevel].registered += 1;
    if (s.voteStatus === "Casted") byGrade[s.gradeLevel].casted += 1;
  }
  const registered = students.length;
  const casted = students.filter((s) => s.voteStatus === "Casted").length;
  return {
    registered,
    casted,
    notCast: registered - casted,
    candidates: candidates.length,
    turnout: registered ? Math.round((casted / registered) * 1000) / 10 : 0,
    byGrade
  };
}

// ---------------------------------------------------------------------------
// Staff accounts
// ---------------------------------------------------------------------------
export const STAFF_DOMAIN = "staff.fresys-tcnhs.app";
export const usernameToEmail = (username) => `${String(username).trim().toLowerCase()}@${STAFF_DOMAIN}`;

export async function createStaffProfile(uid, { name, username, role }) {
  await setDoc(doc(db, COLLECTIONS.staff, uid), {
    name, username: String(username).trim().toLowerCase(), role,
    status: "Pending", createdAt: serverTimestamp()
  });
}

export async function getStaffProfile(uid) {
  const snap = await getDoc(doc(db, COLLECTIONS.staff, uid));
  return snap.exists() ? { id: snap.id, ...snap.data() } : null;
}

export async function listStaff() {
  const snap = await getDocs(collection(db, COLLECTIONS.staff));
  return snap.docs
    .map((d) => ({ id: d.id, ...d.data() }))
    .sort((a, b) => (a.status || "").localeCompare(b.status || "") || (a.name || "").localeCompare(b.name || ""));
}

export async function deleteStaffProfile(uid) {
  await deleteDoc(doc(db, COLLECTIONS.staff, uid));
}

export async function updateStaffStatus(uid, status) {
  await updateDoc(doc(db, COLLECTIONS.staff, uid), { status });
}

export async function updateStaffProfile(uid, patch) {
  await updateDoc(doc(db, COLLECTIONS.staff, uid), patch);
}

// ---------------------------------------------------------------------------
// Archive
// ---------------------------------------------------------------------------
export async function archiveElection({ electionName, academicYear, stats, results, archivedBy }) {
  const ref = await addDoc(collection(db, COLLECTIONS.archive), {
    electionName, academicYear, stats, results,
    archivedBy: archivedBy || (auth.currentUser && auth.currentUser.email) || "system",
    archivedAt: serverTimestamp()
  });
  return ref.id;
}

export async function listArchive() {
  const snap = await getDocs(collection(db, COLLECTIONS.archive));
  return snap.docs
    .map((d) => ({ id: d.id, ...d.data() }))
    .sort((a, b) => (b.archivedAt?.seconds || 0) - (a.archivedAt?.seconds || 0));
}

export async function deleteArchive(id) {
  await deleteDoc(doc(db, COLLECTIONS.archive, id));
}

export { deleteField };
