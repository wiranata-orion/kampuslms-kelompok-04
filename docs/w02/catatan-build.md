# Catatan build Minggu 2

Rancangan untuk modul **mata kuliah** dengan satu `CourseController`:

| Method | URI | Nama Route | Controller@Method | Akses | 
|--------|-----|------------|-------------------|-------|
| **GET** | `/course` | `course.index` | `CourseController@index` | admin, dosen, mahasiswa |
| **GET** | `/course/create` | `course.create` | `CourseController@create` | admin |
| **POST** | `/course` | `course.store` | `CourseController@store` | admin |
| **GET** | `/course/{course}` | `course.show` | `CourseController@show` | admin, dosen, mahasiswa |
| **GET** | `/course/{course}/edit` | `course.edit` | `CourseController@edit` | admin, dosen pengampu |
| **PUT/PATCH** | `/course/{course}` | `course.update` | `CourseController@update` | admin, dosen pengampu |
| **DELETE** | `/course/{course}` | `course.destroy` | `CourseController@destroy` | admin |

Planning noted:
