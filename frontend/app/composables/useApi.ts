// frontend/composables/useApi.ts

export const useApi = () => {
  const config = useRuntimeConfig()
  const baseURL = config.public.apiBase

  interface ApiResponse<T = any> {
    berhasil: boolean
    pesan: string
    data: T
    kesalahan: any
  }

  const handleRequest = async <T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<ApiResponse<T>> => {
    try {
      const response = await fetch(`${baseURL}${endpoint}`, {
        ...options,
        headers: {
          'Content-Type': 'application/json',
          ...options.headers,
        },
      })

      const data = await response.json()
      return data
    } catch (error) {
      console.error('API Error:', error)
      return {
        berhasil: false,
        pesan: 'Terjadi kesalahan koneksi',
        data: null as any,
        kesalahan: error,
      }
    }
  }

  // Karyawan API
  const karyawan = {
    getAll: (filters: Record<string, any> = {}) => {
      const params = new URLSearchParams(filters).toString()
      return handleRequest(`/karyawan/get.php${params ? `?${params}` : ''}`)
    },

    getById: (id: string) => {
      return handleRequest(`/karyawan/get.php?id=${id}`)
    },

    create: async (data: FormData) => {
      try {
        const response = await fetch(`${baseURL}/karyawan/create.php`, {
          method: 'POST',
          body: data,
        })
        return await response.json()
      } catch (error) {
        return {
          berhasil: false,
          pesan: 'Gagal membuat karyawan',
          data: null,
          kesalahan: error,
        }
      }
    },

    update: async (data: FormData) => {
      try {
        const response = await fetch(`${baseURL}/karyawan/update.php`, {
          method: 'POST',
          body: data,
        })
        return await response.json()
      } catch (error) {
        return {
          berhasil: false,
          pesan: 'Gagal mengupdate karyawan',
          data: null,
          kesalahan: error,
        }
      }
    },

    delete: (id: string) => {
      return handleRequest(`/karyawan/delete.php`, {
        method: 'DELETE',
        body: JSON.stringify({ id_karyawan: id }),
      })
    },

    // ============================================
    // EXPORT PDF - FIXED
    // ============================================
    exportPDF: async (filters: Record<string, any> = {}) => {
      try {
        const params = new URLSearchParams(filters).toString()
        const response = await fetch(
          `${baseURL}/karyawan/export_pdf.php${params ? `?${params}` : ''}`,
          {
            method: 'GET',
          }
        )

        // Cek content type untuk menentukan apakah response adalah file atau JSON error
        const contentType = response.headers.get('content-type')
        
        if (!response.ok || (contentType && contentType.includes('application/json'))) {
          // Response adalah JSON error
          const error = await response.json()
          return {
            berhasil: false,
            pesan: error.pesan || 'Gagal export PDF',
            data: null,
            kesalahan: error,
          }
        }

        // Response adalah file PDF
        const blob = await response.blob()
        
        // Cek apakah blob kosong
        if (blob.size === 0) {
          return {
            berhasil: false,
            pesan: 'File PDF kosong atau tidak ada data',
            data: null,
            kesalahan: null,
          }
        }

        // Download file
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `data_karyawan_${new Date().getTime()}.pdf`
        document.body.appendChild(a)
        a.click()
        
        // Cleanup
        setTimeout(() => {
          window.URL.revokeObjectURL(url)
          document.body.removeChild(a)
        }, 100)

        return {
          berhasil: true,
          pesan: 'Export PDF berhasil',
          data: null,
          kesalahan: null,
        }
      } catch (error) {
        console.error('Export PDF Error:', error)
        return {
          berhasil: false,
          pesan: 'Gagal export PDF: ' + (error as Error).message,
          data: null,
          kesalahan: error,
        }
      }
    },

    // ============================================
    // EXPORT EXCEL - FIXED
    // ============================================
    exportExcel: async (filters: Record<string, any> = {}) => {
      try {
        const params = new URLSearchParams(filters).toString()
        const response = await fetch(
          `${baseURL}/karyawan/export_excel.php${params ? `?${params}` : ''}`,
          {
            method: 'GET',
          }
        )

        // Cek content type untuk menentukan apakah response adalah file atau JSON error
        const contentType = response.headers.get('content-type')
        
        if (!response.ok || (contentType && contentType.includes('application/json'))) {
          // Response adalah JSON error
          const error = await response.json()
          return {
            berhasil: false,
            pesan: error.pesan || 'Gagal export Excel',
            data: null,
            kesalahan: error,
          }
        }

        // Response adalah file Excel
        const blob = await response.blob()
        
        // Cek apakah blob kosong
        if (blob.size === 0) {
          return {
            berhasil: false,
            pesan: 'File Excel kosong atau tidak ada data',
            data: null,
            kesalahan: null,
          }
        }

        // Download file
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `data_karyawan_${new Date().getTime()}.xlsx`
        document.body.appendChild(a)
        a.click()
        
        // Cleanup
        setTimeout(() => {
          window.URL.revokeObjectURL(url)
          document.body.removeChild(a)
        }, 100)

        return {
          berhasil: true,
          pesan: 'Export Excel berhasil',
          data: null,
          kesalahan: null,
        }
      } catch (error) {
        console.error('Export Excel Error:', error)
        return {
          berhasil: false,
          pesan: 'Gagal export Excel: ' + (error as Error).message,
          data: null,
          kesalahan: error,
        }
      }
    },

    // ============================================
    // IMPORT EXCEL
    // ============================================
    importExcel: async (file: File) => {
      try {
        const formData = new FormData()
        formData.append('file', file)

        const response = await fetch(`${baseURL}/karyawan/import_excel.php`, {
          method: 'POST',
          body: formData,
        })

        const data = await response.json()
        return data
      } catch (error) {
        console.error('Import Excel Error:', error)
        return {
          berhasil: false,
          pesan: 'Gagal import Excel',
          data: null,
          kesalahan: error,
        }
      }
    },
  }

  // Jabatan API
  const jabatan = {
    getAll: (filters: Record<string, any> = {}) => {
      const params = new URLSearchParams(filters).toString()
      return handleRequest(`/jabatan/get.php${params ? `?${params}` : ''}`)
    },

    getById: (id: number) => {
      return handleRequest(`/jabatan/get.php?id=${id}`)
    },
  }

  return {
    karyawan,
    jabatan,
  }
}