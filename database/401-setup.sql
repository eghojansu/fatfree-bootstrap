--
-- @desc Insert initial setup
--

SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `Configurations`
    (`Name`, `Content`, `CreatedAt`)
VALUES
    ("Maintenance", "off", NOW()),
    ("AppTitle", "Web App", NOW()),
    ("AppAlias", "Wapp", NOW());

INSERT INTO `Posts`
    (`Slug`, `Title`, `Headline`, `Content`, `Type`, `CreatedAt`)
VALUES
    ("welcome", "Welcome", "welcome", "Welcome Guest!", "welcome", NOW()),
    ("about", "About Us", "about", "About us", "about", NOW());

SET FOREIGN_KEY_CHECKS=1;
