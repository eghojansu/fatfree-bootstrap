# fatfree-bootstrap
Fatfree simple fast bootstrap, with some usefull helper and abstract classes

> Note: you must have [composer](http://getcomposer.com "Composer") and [git](https://git-scm.com/ "GIT") installed on your machine

## howto
execute

```console
composer create-project eghojansu/fatfree-bootstrap [application-path]
```

then you can start develop your apps.

### Archive project (using git)

This is a simple command

```console
git archive -o path/to/filename.zip --prefix=pathprefix/ [version tag]
```

### Installation

1. Copy folder file ini ke htdocs/www (sesuaikan server)
2. Buat database menggunakan phpmyadmin, kemudian import schema pada folder app/schema sesuai urutannya
3. Edit file app/config/app.ini pada bagian mysql, sesuaikan setting database-nya
4. Akses http://localhost/{nama-folder-file-ini}
5. Done


Happy code,


fa/eghojansu
