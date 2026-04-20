/proyek_pt_nt
│
├── config/
│   └── database.php
│
├── public/
│   ├── index.php
│   ├── tambah.php
│   ├── edit.php
│   ├── hapus.php
│   ├── detail.php
│   ├── list.php
│   └── upload/
│       └── foto/
│
├── api/
│   ├── karyawan/
│   │   ├── get.php
│   │   ├── create.php
│   │   ├── update.php
│   │   └── delete.php
│   |── jabatan/
│   |    └── get.php
|   |--boostrap.php
│
├── functions/
│   ├── karyawan.php
│   ├── jabatan.php
│   ├── response.php
│   └── logger.php
│
├── logs/
│   └── akses.log
│
└── README.md

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
      return handleRequest(`/../../api/karyawan/get.php${params ? `?${params}` : ''}`)
    },

    getById: (id: string) => {
      return handleRequest(`/../../api/karyawan/get.php?id=${id}`)
    },

    create: async (data: FormData) => {
      try {
        const response = await fetch(`${baseURL}/../../api/karyawan/create.php`, {
          method: 'POST',
          body: data, // FormData untuk upload file
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
        const response = await fetch(`${baseURL}/../../api/karyawan/update.php`, {
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
      return handleRequest(`/../../api/karyawan/delete.php`, {
        method: 'DELETE',
        body: JSON.stringify({ id_karyawan: id }),
      })
    },
  }

  // Jabatan API
  const jabatan = {
    getAll: (filters: Record<string, any> = {}) => {
      const params = new URLSearchParams(filters).toString()
      return handleRequest(`/../../api/jabatan/get.php${params ? `?${params}` : ''}`)
    },

    getById: (id: number) => {
      return handleRequest(`/../../api/jabatan/get.php?id=${id}`)
    },
  }

  return {
    karyawan,
    jabatan,
  }
}