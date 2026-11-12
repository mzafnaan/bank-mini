<?php

namespace App\Services;

use ZipArchive;

class SimpleXlsxExportService
{
    /**
     * Generate a genuine OpenXML .xlsx file.
     *
     * @param string $filePath Full path to save the .xlsx file
     * @param array $metaPairs Array of metadata pairs: [['L_label' => ..., 'L_val' => ..., 'R_label' => ..., 'R_val' => ...], ...]
     * @param array $headers Column header titles
     * @param array $rows Array of rows, each containing ['v' => value, 's' => styleIndex]
     * @param array|null $summary Summary total row
     * @return bool
     */
    public static function create(string $filePath, array $metaPairs, array $headers, array $rows, ?array $summary = null): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n"
            . '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n"
            . '  <Default Extension="xml" ContentType="application/xml"/>' . "\n"
            . '  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n"
            . '  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n"
            . '  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . "\n"
            . '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n"
            . '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' . "\n"
            . '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n"
            . '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' . "\n"
            . '  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' . "\n"
            . '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // 4. xl/workbook.xml
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n"
            . '  <sheets>' . "\n"
            . '    <sheet name="Mutasi Rekening" sheetId="1" r:id="rId1"/>' . "\n"
            . '  </sheets>' . "\n"
            . '</workbook>';
        $zip->addFromString('xl/workbook.xml', $wb);

        // 5. xl/styles.xml
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n"
            . '  <numFmts count="1">' . "\n"
            . '    <numFmt numFmtId="164" formatCode="#,##0"/>' . "\n"
            . '  </numFmts>' . "\n"
            . '  <fonts count="5">' . "\n"
            . '    <!-- 0: Regular 10pt --> <font><sz val="10"/><name val="Arial"/><color rgb="FF1E293B"/></font>' . "\n"
            . '    <!-- 1: Bold 10pt --> <font><b/><sz val="10"/><name val="Arial"/><color rgb="FF1E293B"/></font>' . "\n"
            . '    <!-- 2: Title Bold 16pt --> <font><b/><sz val="16"/><name val="Arial"/><color rgb="FF0F172A"/></font>' . "\n"
            . '    <!-- 3: Header White Bold 10pt --> <font><b/><sz val="10"/><name val="Arial"/><color rgb="FFFFFFFF"/></font>' . "\n"
            . '    <!-- 4: Blue Bold 10pt --> <font><b/><sz val="10"/><name val="Arial"/><color rgb="FF1D4ED8"/></font>' . "\n"
            . '  </fonts>' . "\n"
            . '  <fills count="5">' . "\n"
            . '    <fill><patternFill patternType="none"/></fill>' . "\n"
            . '    <fill><patternFill patternType="gray125"/></fill>' . "\n"
            . '    <!-- 2: Dark Slate Header --> <fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/></patternFill></fill>' . "\n"
            . '    <!-- 3: Soft Gray Box --> <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' . "\n"
            . '    <!-- 4: Summary Fill --> <fill><patternFill patternType="solid"><fgColor rgb="FFE2E8F0"/></patternFill></fill>' . "\n"
            . '  </fills>' . "\n"
            . '  <borders count="3">' . "\n"
            . '    <border><left/><right/><top/><bottom/><diagonal/></border>' . "\n"
            . '    <!-- 1: Thin Gray --> <border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border>' . "\n"
            . '    <!-- 2: Dark Header --> <border><left style="thin"><color rgb="FF0F172A"/></left><right style="thin"><color rgb="FF0F172A"/></right><top style="thin"><color rgb="FF0F172A"/></top><bottom style="thin"><color rgb="FF0F172A"/></bottom><diagonal/></border>' . "\n"
            . '  </borders>' . "\n"
            . '  <cellStyleXfs count="1">' . "\n"
            . '    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' . "\n"
            . '  </cellStyleXfs>' . "\n"
            . '  <cellXfs count="15">' . "\n"
            . '    <!-- 0: Normal --> <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' . "\n"
            . '    <!-- 1: Title (Bold 16pt, Left) --> <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0"><alignment horizontal="left" vertical="center"/></xf>' . "\n"
            . '    <!-- 2: Subtitle (Left) --> <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment horizontal="left" vertical="center"/></xf>' . "\n"
            . '    <!-- 3: Meta Label Box (Bold, Soft Gray, Border 1) --> <xf numFmtId="0" fontId="1" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment vertical="center"/></xf>' . "\n"
            . '    <!-- 4: Meta Value Box (Regular, Border 1) --> <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="center"/></xf>' . "\n"
            . '    <!-- 5: Meta Value Bold Blue (Bold Blue, Border 1) --> <xf numFmtId="0" fontId="4" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"><alignment vertical="center"/></xf>' . "\n"
            . '    <!-- 6: Table Header (Dark Slate, White Bold, Border 2, Center) --> <xf numFmtId="0" fontId="3" fillId="2" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n"
            . '    <!-- 7: Data Left (Border 1) --> <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="center"/></xf>' . "\n"
            . '    <!-- 8: Data Center (Border 1) --> <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n"
            . '    <!-- 9: Data Number (Border 1, #,##0, Right) --> <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n"
            . '    <!-- 10: Data Number Bold (Border 1, #,##0, Right) --> <xf numFmtId="164" fontId="1" fillId="0" borderId="1" xfId="0" applyFont="1" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n"
            . '    <!-- 11: Summary Label (Bold, Fill 4, Border 1, Right) --> <xf numFmtId="0" fontId="1" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n"
            . '    <!-- 12: Summary Number (Bold, Fill 4, Border 1, #,##0, Right) --> <xf numFmtId="164" fontId="1" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n"
            . '    <!-- 13: Data Alt Row Left (Border 1, Fill 3) --> <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment vertical="center"/></xf>' . "\n"
            . '    <!-- 14: Data Alt Row Center (Border 1, Fill 3) --> <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n"
            . '  </cellXfs>' . "\n"
            . '</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // 6. xl/worksheets/sheet1.xml
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetViews><sheetView tabSelected="1" workbookViewId="0" showGridLines="true"/></sheetViews>' . "\n";

        // Column widths
        $xml .= '  <cols>' . "\n";
        $colWidths = [1 => 7, 2 => 18, 3 => 15, 4 => 18, 5 => 16, 6 => 16, 7 => 18, 8 => 15];
        foreach ($colWidths as $idx => $w) {
            $xml .= "    <col min=\"{$idx}\" max=\"{$idx}\" width=\"{$w}\" customWidth=\"1\"/>\n";
        }
        $xml .= '  </cols>' . "\n";

        $xml .= '  <sheetData>' . "\n";

        $rowIdx = 1;
        $mergeList = [];

        // Row 1: Title "Bank Mini Sekolah" (Left-aligned!)
        $xml .= "    <row r=\"{$rowIdx}\" ht=\"26\" customHeight=\"1\">\n";
        $xml .= "      <c r=\"A{$rowIdx}\" s=\"1\" t=\"inlineStr\"><is><t>Bank Mini Sekolah</t></is></c>\n";
        $xml .= "    </row>\n";
        $rowIdx++;

        // Row 2: Subtitle
        $xml .= "    <row r=\"{$rowIdx}\" ht=\"18\" customHeight=\"1\">\n";
        $xml .= "      <c r=\"A{$rowIdx}\" s=\"2\" t=\"inlineStr\"><is><t>SMK Negeri 1 Bantul • Laporan Mutasi Transaksi</t></is></c>\n";
        $xml .= "    </row>\n";
        $rowIdx++;

        // Row 3: Blank separator
        $rowIdx++;

        // Metadata block (2-column card layout across A-H)
        foreach ($metaPairs as $pair) {
            $xml .= "    <row r=\"{$rowIdx}\" ht=\"20\" customHeight=\"1\">\n";
            // Left pair: Label in A (merged A-B), Val in C (merged C-D)
            $xml .= "      <c r=\"A{$rowIdx}\" s=\"3\" t=\"inlineStr\"><is><t>" . htmlspecialchars((string)($pair['L_label'] ?? '')) . "</t></is></c>\n";
            $xml .= "      <c r=\"B{$rowIdx}\" s=\"3\"/>\n";
            $xml .= "      <c r=\"C{$rowIdx}\" s=\"4\" t=\"inlineStr\"><is><t>" . htmlspecialchars((string)($pair['L_val'] ?? '')) . "</t></is></c>\n";
            $xml .= "      <c r=\"D{$rowIdx}\" s=\"4\"/>\n";

            // Right pair: Label in E (merged E-F), Val in G (merged G-H)
            $rStyle = (!empty($pair['R_is_acc'])) ? 5 : 4;
            $xml .= "      <c r=\"E{$rowIdx}\" s=\"3\" t=\"inlineStr\"><is><t>" . htmlspecialchars((string)($pair['R_label'] ?? '')) . "</t></is></c>\n";
            $xml .= "      <c r=\"F{$rowIdx}\" s=\"3\"/>\n";
            $xml .= "      <c r=\"G{$rowIdx}\" s=\"{$rStyle}\" t=\"inlineStr\"><is><t>" . htmlspecialchars((string)($pair['R_val'] ?? '')) . "</t></is></c>\n";
            $xml .= "      <c r=\"H{$rowIdx}\" s=\"{$rStyle}\"/>\n";

            $xml .= "    </row>\n";

            $mergeList[] = "A{$rowIdx}:B{$rowIdx}";
            $mergeList[] = "C{$rowIdx}:D{$rowIdx}";
            $mergeList[] = "E{$rowIdx}:F{$rowIdx}";
            $mergeList[] = "G{$rowIdx}:H{$rowIdx}";

            $rowIdx++;
        }

        // Row blank separator
        $rowIdx++;

        // Table Header
        $xml .= "    <row r=\"{$rowIdx}\" ht=\"24\" customHeight=\"1\">\n";
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($headers as $hIdx => $hText) {
            $colLetter = $cols[$hIdx] ?? 'A';
            $xml .= "      <c r=\"{$colLetter}{$rowIdx}\" s=\"6\" t=\"inlineStr\"><is><t>" . htmlspecialchars((string)$hText) . "</t></is></c>\n";
        }
        $xml .= "    </row>\n";
        $rowIdx++;

        // Table Rows
        foreach ($rows as $rIdx => $r) {
            $xml .= "    <row r=\"{$rowIdx}\" ht=\"19\" customHeight=\"1\">\n";
            foreach ($r as $cIdx => $cell) {
                $colLetter = $cols[$cIdx] ?? 'A';
                $val = $cell['v'] ?? '';
                $style = $cell['s'] ?? 7;
                $isNum = ($style == 9 || $style == 10 || $style == 12);

                if ($isNum && is_numeric($val)) {
                    $xml .= "      <c r=\"{$colLetter}{$rowIdx}\" s=\"{$style}\"><v>{$val}</v></c>\n";
                } else {
                    $xml .= "      <c r=\"{$colLetter}{$rowIdx}\" s=\"{$style}\" t=\"inlineStr\"><is><t>" . htmlspecialchars((string)$val) . "</t></is></c>\n";
                }
            }
            $xml .= "    </row>\n";
            $rowIdx++;
        }

        // Summary Row
        if ($summary) {
            $xml .= "    <row r=\"{$rowIdx}\" ht=\"24\" customHeight=\"1\">\n";
            foreach ($summary as $cIdx => $cell) {
                $colLetter = $cols[$cIdx] ?? 'A';
                $val = $cell['v'] ?? '';
                $style = $cell['s'] ?? 11;
                $isNum = ($style == 12);

                if ($isNum && is_numeric($val)) {
                    $xml .= "      <c r=\"{$colLetter}{$rowIdx}\" s=\"{$style}\"><v>{$val}</v></c>\n";
                } else {
                    $xml .= "      <c r=\"{$colLetter}{$rowIdx}\" s=\"{$style}\" t=\"inlineStr\"><is><t>" . htmlspecialchars((string)$val) . "</t></is></c>\n";
                }
            }
            $xml .= "    </row>\n";

            // Merge A..D for "TOTAL PERIODE"
            $mergeList[] = "A{$rowIdx}:D{$rowIdx}";
            $rowIdx++;
        }

        $xml .= '  </sheetData>' . "\n";

        // Merged Cells
        if (!empty($mergeList)) {
            $count = count($mergeList);
            $xml .= "  <mergeCells count=\"{$count}\">\n";
            foreach ($mergeList as $m) {
                $xml .= "    <mergeCell ref=\"{$m}\"/>\n";
            }
            $xml .= "  </mergeCells>\n";
        }

        $xml .= '</worksheet>' . "\n";

        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();

        return true;
    }
}
