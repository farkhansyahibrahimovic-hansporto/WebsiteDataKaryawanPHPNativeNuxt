Cara menggunakan api di postman

Jabatan Get:
http://localhost/bispro1/backend/api/jabatan/(Broser)
GET http://localhost/bispro1/backend/api/jabatan/?id=3(Postman)

Karyawan Get:
http://localhost/bispro1/backend/api/karyawan/(Broser)
GET http://localhost/bispro1/backend/api/karyawan/?id=10(Postman)

Karyawan Create
🔹 Setting Postman
Method: POST
URL:http://localhost/bispro1/backend/api/karyawan/create.php
Tab Body

Pilih form-data
🔹 Isi field
Key	            Type	Contoh
nama	        Text	Budi Santoso
nik	            Text	3201123456
jenis_kelamin	Text	L
tanggal_lahir	Text	1995-03-12
jabatan_id	    Text	2
salary	        Text	7500000
foto	        File	pilih gambar

Karyawan Delete
🔹 Setting Postman
Method: DELETE
URL:http://localhost/bispro1/backend/api/karyawan/delete.php
Tab Body

Pilih raw
Format → JSON
🔹 Isi Body
{
  "id_karyawan": 10
}
🔹 Klik Send
🔹 Response sukses

Karyawan Update
🔹 Setting
Method: POST
URL:http://localhost/bispro1/backend/api/karyawan/update.php
Body → form-data

🔹 Field
Key	        Type	Contoh
id_karyawan	Text	10
nama	    Text	Budi Santoso
jabatan_id	Text	3
salary	    Text	9000000
old_foto	Text	budi_lama.jpg
foto	    File	pilih foto baru

📌 old_foto wajib jika ingin hapus foto lama
📌 foto opsional

🔹 Response
{
  "status": true,
  "message": "Data diupdate"
}

Karyawan E_Excel
http://localhost/bispro1/backend/api/karyawan/export_excel.php(Browser)
http://localhost/bispro1/backend/api/karyawan/export_excel.php
?jabatan_id=2
&jenis_kelamin=L
&salary_min=6000000
(Dengan Filter)

🔹 Setting
Method: GET
URL:http://localhost/bispro1/backend/api/karyawan/export_excel.php
Tab Params → isi filter jika perlu

Klik Send
🔹 Hasil
Postman akan download file .xlsx
Klik Save Response → Save to file
📌 Jangan pilih Body → raw, karena ini GET

Karyawan E_PDF
http://localhost/bispro1/backend/api/karyawan/export_pdf.php(Browser)

?jabatan_id=2
&jenis_kelamin=L
&salary_min=6000000
&salary_max=10000000
&age_min=25
&age_max=40
&q=andi
http://localhost/bispro1/backend/api/karyawan/export_pdf.php?jabatan_id=2(Dengan Filter)

🔹 Setting
Method: GET
URL:http://localhost/bispro1/backend/api/karyawan/export_pdf.php
Tab Params → isi filter
Klik Send

🔹 Hasil
Postman menerima binary PDF
Klik Save Response → Save to file
📌 Tidak ada JSON response jika sukses

Karyawan I_Excel
Setting Postman
Method: POST
URL:http://localhost/bispro1/backend/api/karyawan/import_excel.php
Tab Body

Pilih form-data
Tambahkan field:

Key	Type	Value
file	    File	pilih file .xlsx

Klik Send

🔹 Response sukses
{
  "berhasil": true,
  "pesan": "Import selesai. Sukses: 18, Gagal: 2",
  "data": {
    "result": {
      "total": 20,
      "sukses": 18,
      "gagal": 2
    },
    "errors": [],
    "summary": {
      "total_processed": 20,
      "success": 18,
      "failed": 2,
      "success_rate": "90%"
    }
  }
}