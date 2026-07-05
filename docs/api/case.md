1. Topik: Laravel Authentication 
a. Buat 1 landing page dengan akses public, yang dapat dibuka tanpa harus login 
b. Buat 1 page dashboard, yang hanya dapat diakses jika sudah login, dengan role apapun 
c. Buat 1 page untuk CRUD role management, yang hanya dapat diakses jika sudah login, 
dengan role apapun 
d. Buat 1 page untuk CRUD user account, yang hanya dapat diakses jika sudah login 
sebagai role "Administrator" 
2. Topik: Laravel Basic CRUD & Relationship 
a. Buat minimal 3 form untuk CRUD yang saling memiliki relationship dengan ketentuan 
minimal sebagai berikut: 
i. 
ii. 
iii. 
iv. 
v. 
vi. 
vii. 
Study/use case bebas 
Ada minimal 3 table, dan harus menggunakan field wajib berikut: UUID, datetime, 
boolean, json 
Semua table harus ada relationship dan ter-implementasi juga di penggunaannya 
pada front-end 
Implementasi soft-deletes 
Implementasi upload file/attachment, dengan ketentuan: file pdf only, filesize 
between 100 kb - 500 kb 
Implementasi select2/option yang datasourcenya diambil dari table database 
Implementasi searching/filtering dan sorting 
3. Topik: Laravel Audits dan Auditable 
a. Implementasikan Audits pada semua form yang dibuat pada soal nomor 2 
b. Tampilkan audit-trail pada setiap form yang sudah mengimplementasikan Audits, 
dengan contoh seperti ini: 
c. Implementasikan konsep Audits dan Auditable pada semua form, yang expected 
resultnya adalah semua transaksional atau data historical yang sudah diinput tidak 
boleh berubah walaupun data induk/master diupdate 
4. Topik: Excel Export & Import 
a. Dari seluruh form yang dibuat di atas, sediakan fitur export dan import dengan file excel 
b. Fitur dynamic field saat proses export dan import file excel 
c. Gunakan fitur QUEUE agar proses export/import dijalankan di background 