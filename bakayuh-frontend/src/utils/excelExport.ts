import * as XLSX from 'xlsx'

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
 * Ekspor data ke format file Microsoft Excel (.xlsx) murni.
 * Memastikan tabel rapi, kolom terpisah dengan lebar yang proporsional,
 * dan tidak ada peringatan 'Possible data loss' dari Excel.
 */
export function exportToExcel(options: ExcelExportOptions) {
  const { fileName, sheetName = 'Sheet1', title, subtitle, headers, rows, colWidths } = options

  // Siapkan data 2D array
  const sheetData: (string | number)[][] = []

  // Judul & Subjudul dokumen jika ada
  if (title) {
    sheetData.push([title])
    if (subtitle) {
      sheetData.push([subtitle])
    }
    sheetData.push([]) // baris kosong pemisah
  }

  // Baris Header Kolom
  sheetData.push(headers)

  // Baris Data
  for (const row of rows) {
    // Bersihkan kutip pembungkus jika ada
    const cleanedRow = row.map((val) => {
      if (typeof val === 'string') {
        // Hapus tanda kutip luar jika dibungkus manual
        if (val.startsWith('"') && val.endsWith('"') && val.length >= 2) {
          return val.slice(1, -1).replace(/""/g, '"')
        }
      }
      return val ?? ''
    })
    sheetData.push(cleanedRow)
  }

  // Buat worksheet dari array
  const ws = XLSX.utils.aoa_to_sheet(sheetData)

  // Tentukan lebar kolom (colWidths)
  if (colWidths && colWidths.length > 0) {
    ws['!cols'] = colWidths.map((w) => ({ wch: w }))
  } else {
    // Hitung otomatis lebar kolom optimal
    const widths = headers.map((h, colIdx) => {
      let maxLen = h.length
      for (const r of rows) {
        const val = r[colIdx] != null ? String(r[colIdx]) : ''
        if (val.length > maxLen) {
          maxLen = Math.min(val.length, 60)
        }
      }
      return Math.max(maxLen + 3, 10)
    })
    ws['!cols'] = widths.map((w) => ({ wch: w }))
  }

  // Buat workbook
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, sheetName.substring(0, 31))

  // Pastikan ekstensi .xlsx
  const finalFileName = fileName.endsWith('.xlsx') ? fileName : `${fileName}.xlsx`

  // Tulis dan unduh file
  XLSX.writeFile(wb, finalFileName)
}

/**
 * Ekspor data ke format CSV dengan kompatibilitas Microsoft Excel Windows (Regional Indonesia).
 * Menggunakan BOM \uFEFF dan direktif sep=, agar Excel otomatis memecah kolom
 * dan tidak menumpuk semua data ke kolom A.
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

  // Direktif 'sep=,' di baris pertama membuat Microsoft Excel versi apapun
  // langsung mengenali delimiter koma dan membagi data ke kolom A, B, C, dst.
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
