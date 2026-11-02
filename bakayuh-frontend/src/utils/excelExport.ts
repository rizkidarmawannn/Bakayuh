import ExcelJS from 'exceljs'

export interface ExcelExportOptions {
  fileName: string
  sheetName?: string
  title?: string
  subtitle?: string
  headers: string[]
  rows: (string | number)[][]
  colWidths?: number[]
}

/**
 * Ekspor data ke format file Microsoft Excel (.xlsx) dengan tabel bergaris (borders),
 * warna header resmi Kanwil Kemenkumham, perataan sel otomatis, dan wrapping teks.
 */
export async function exportToExcel(options: ExcelExportOptions) {
  const { fileName, sheetName = 'Sheet1', title, subtitle, headers, rows, colWidths } = options

  const workbook = new ExcelJS.Workbook()
  workbook.creator = 'BAKAYUH Kanwil Kemenkumham Kalsel'
  workbook.created = new Date()

  const worksheet = workbook.addWorksheet(sheetName.substring(0, 31), {
    views: [{ showGridLines: true }],
  })

  let currentRowIdx = 1

  // 1. KOP JUDUL DOKUMEN (Jika Ada)
  if (title) {
    const titleRow = worksheet.getRow(currentRowIdx)
    titleRow.getCell(1).value = title
    titleRow.getCell(1).font = {
      name: 'Calibri',
      size: 13,
      bold: true,
      color: { argb: 'FF0B2A6F' },
    }
    titleRow.getCell(1).alignment = { vertical: 'middle', horizontal: 'left' }
    titleRow.height = 24
    currentRowIdx++

    if (subtitle) {
      const subRow = worksheet.getRow(currentRowIdx)
      subRow.getCell(1).value = subtitle
      subRow.getCell(1).font = {
        name: 'Calibri',
        size: 10,
        italic: true,
        color: { argb: 'FF475569' },
      }
      subRow.getCell(1).alignment = { vertical: 'middle', horizontal: 'left' }
      subRow.height = 18
      currentRowIdx++
    }

    // Baris kosong pemisah
    currentRowIdx++
  }

  // 2. HEADER TABEL
  const headerRowIdx = currentRowIdx
  const headerRow = worksheet.getRow(headerRowIdx)
  headerRow.values = headers
  headerRow.height = 28

  // Style Header
  for (let c = 1; c <= headers.length; c++) {
    const cell = headerRow.getCell(c)
    const headerName = String(cell.value || '')

    // Deteksi kolom Triwulan / Rekap untuk warna pembeda (Orange / Navy)
    const isTwColumn =
      headerName.toLowerCase().includes('tw ') ||
      headerName.toLowerCase().includes('triwulan') ||
      headerName.toLowerCase().includes('semester')

    cell.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: isTwColumn ? 'FFEA580C' : 'FF1D4ED8' }, // Orange or Royal Blue
    }
    cell.font = {
      name: 'Calibri',
      size: 10,
      bold: true,
      color: { argb: 'FFFFFFFF' },
    }
    cell.alignment = {
      vertical: 'middle',
      horizontal: 'center',
      wrapText: true,
    }
    cell.border = {
      top: { style: 'medium', color: { argb: 'FF0B2A6F' } },
      bottom: { style: 'medium', color: { argb: 'FF0B2A6F' } },
      left: { style: 'thin', color: { argb: 'FFFFFFFF' } },
      right: { style: 'thin', color: { argb: 'FFFFFFFF' } },
    }
  }

  currentRowIdx++

  // 3. BARIS DATA TABEL (DENGAN GARIS TABEL & STRIPING)
  const isNumericOrShort = (header: string) => {
    const h = header.toLowerCase()
    return (
      h === 'no' ||
      h === 'unit' ||
      h === 'target' ||
      h === 'jan' ||
      h === 'feb' ||
      h === 'mar' ||
      h === 'apr' ||
      h === 'mei' ||
      h === 'jun' ||
      h === 'jul' ||
      h === 'agu' ||
      h === 'sep' ||
      h === 'okt' ||
      h === 'nov' ||
      h === 'des' ||
      h.startsWith('rekap') ||
      h.startsWith('s.d.') ||
      h.includes('progres') ||
      h.includes('bobot') ||
      h.includes('nilai')
    )
  }

  rows.forEach((rowValues, rIdx) => {
    const dataRow = worksheet.getRow(currentRowIdx)
    const isZebra = rIdx % 2 === 1

    for (let c = 1; c <= headers.length; c++) {
      let rawVal = rowValues[c - 1]
      // Bersihkan kutip pembungkus jika ada
      if (typeof rawVal === 'string' && rawVal.startsWith('"') && rawVal.endsWith('"')) {
        rawVal = rawVal.slice(1, -1).replace(/""/g, '"')
      }

      const cell = dataRow.getCell(c)
      cell.value = rawVal ?? ''

      const headerName = headers[c - 1] || ''
      const isShort = isNumericOrShort(headerName)

      // Styling Sel Data
      cell.font = {
        name: 'Calibri',
        size: 10,
        color: { argb: 'FF1E293B' },
      }

      cell.alignment = {
        vertical: 'middle',
        horizontal: isShort ? 'center' : 'left',
        wrapText: !isShort,
      }

      // Garis border tabel lengkap di setiap sel
      cell.border = {
        top: { style: 'thin', color: { argb: 'FFCBD5E1' } },
        bottom: { style: 'thin', color: { argb: 'FFCBD5E1' } },
        left: { style: 'thin', color: { argb: 'FFCBD5E1' } },
        right: { style: 'thin', color: { argb: 'FFCBD5E1' } },
      }

      // Background selang-seling (zebra)
      if (isZebra) {
        cell.fill = {
          type: 'pattern',
          pattern: 'solid',
          fgColor: { argb: 'FFF8FAFC' },
        }
      }
    }

    dataRow.height = 22
    currentRowIdx++
  })

  // 4. ATUR LEBAR KOLOM
  if (colWidths && colWidths.length > 0) {
    colWidths.forEach((w, idx) => {
      worksheet.getColumn(idx + 1).width = w
    })
  } else {
    headers.forEach((h, colIdx) => {
      let maxLen = h.length
      rows.forEach((r) => {
        const val = r[colIdx] != null ? String(r[colIdx]) : ''
        if (val.length > maxLen) {
          maxLen = Math.min(val.length, 50)
        }
      })
      worksheet.getColumn(colIdx + 1).width = Math.max(maxLen + 3, 10)
    })
  }

  // 5. DOWNLOAD FILE
  const finalFileName = fileName.endsWith('.xlsx') ? fileName : `${fileName}.xlsx`
  const buffer = await workbook.xlsx.writeBuffer()
  const blob = new Blob([buffer], {
    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = finalFileName
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

/**
 * Ekspor data ke format CSV dengan kompatibilitas Microsoft Excel Windows (Regional Indonesia).
 * Menggunakan BOM \uFEFF dan direktif sep=, agar Excel otomatis memecah kolom.
 */
export function exportToCsv(options: {
  fileName: string
  headers: string[]
  rows: (string | number)[][]
}) {
  const { fileName, headers, rows } = options

  const formatCell = (val: string | number) => {
    if (val == null) return '""'
    let str = String(val)
    if (str.startsWith('"') && str.endsWith('"') && str.length >= 2) {
      str = str.slice(1, -1)
    }
    return `"${str.replace(/"/g, '""')}"`
  }

  const lines: string[] = [
    'sep=,',
    headers.map(formatCell).join(','),
    ...rows.map((r) => r.map(formatCell).join(',')),
  ]

  const csvContent = '\uFEFF' + lines.join('\r\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  const finalFileName = fileName.endsWith('.csv') ? fileName : `${fileName}.csv`
  link.setAttribute('download', finalFileName)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}
