--
-- @desc Insert initial setup
--

SET FOREIGN_KEY_CHECKS=0;

INSERT INTO setting
    (nama, konten, created_at)
VALUES
    ("maintenance", "off", NOW()),
    ("appTitle", "Web App", NOW()),
    ("appAlias", "Wapp", NOW());

INSERT INTO post
    (slug, title, headline, konten, tipe, created_at)
VALUES
    ("welcome", "Welcome", "welcome", "Welcome Guest!", "welcome", NOW()),
    ("about", "About Us", "about", "About us", "about", NOW());

SET FOREIGN_KEY_CHECKS=1;
