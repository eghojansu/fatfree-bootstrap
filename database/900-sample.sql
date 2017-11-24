--
-- @desc Insert Sample data
--

SET FOREIGN_KEY_CHECKS=0;

INSERT INTO Posts
    (`Slug`, `Title`, `Headline`, `Content`, `Type`, `CreatedAt`)
VALUES
    ("berita-1", "Berita 1", "Berita 1", "Berita 1", "published", NOW()),
    ("berita-2", "Berita 2", "Berita 2", "Berita 2", "published", NOW()),
    ("berita-3", "Berita 3", "Berita 3", "Berita 3", "published", NOW()),
    ("berita-4", "Berita 4", "Berita 4", "Berita 4", "published", NOW()),
    ("berita-5", "Berita 5", "Berita 5", "Berita 5", "published", NOW());

SET FOREIGN_KEY_CHECKS=1;
