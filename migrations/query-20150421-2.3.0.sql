-- 
-- Set of query to migrate the database to a new version
-- 
-- @filename: query-[date new tag yyyymmdd]-[version gino].sql
-- @database: MySQL, SQL Server
-- 
-- Data query: changes of labels or translations (no essential)
-- Structure query: changes of structure

-- --------------------------------------------------------
-- MySQL
-- --------------------------------------------------------

-- Data query
INSERT INTO `page_entry` (`category_id`, `author`, `creation_date`, `last_edit_date`, `title`, `slug`, `image`, `url_image`, `text`, `tags`, `enable_comments`, `published`, `social`, `private`, `users`, `read`, `tpl_code`, `box_tpl_code`) VALUES
(NULL, 1, '2015-05-11 15:05:21', '2015-05-12 12:40:08', 'Privacy - Cookie', 'privacy-cookie', NULL, NULL, '<div>In merito all''uso di cookie su questo sito, si dichiara che:</div>\r\n\r\n<div> </div>\r\n\r\n<div>1. Continuando la navigazione, si accettano implicitamente i cookie che il sistema installerà.</div>\r\n\r\n<div> </div>\r\n\r\n<div>2. Questo sito NON fa uso di cookie di PROFILAZIONE degli utenti.</div>\r\n\r\n<div> </div>\r\n\r\n<div>3. Questo sito NON consente l''invio di cookie di PROFILAZIONE di TERZE PARTI.</div>\r\n\r\n<div> </div>\r\n\r\n<div>4. Questo sito fa uso esclusivamente di cookie TECNICI per salvare i parametri di sessione e agevolare quindi la navigazione agli utenti. In particolare troverete il cookie [SITE]_SESSID che contiene il codice di sessione utilizzato per l''autenticazione e per la velocizzazione della navigazione.</div>\r\n\r\n<div> </div>\r\n\r\n<div>5. Potranno inoltre essere presenti cookie ESCLUSIVAMENTE TECNICI di terze parti:</div>\r\n\r\n<div> </div>\r\n\r\n<div>Google (Analitycs / Maps / reCAPTCHA)</div>\r\n\r\n<div><a href="http://www.google.com/policies/technologies/types/" style="line-height: 21.6000003814697px;">http://www.google.com/policies/technologies/types/</a></div>\r\n\r\n<div> </div>\r\n\r\n<div>ShareThis</div>\r\n\r\n<div><a href="http://www.sharethis.com/legal/privacy/#sthash.97W6NVSe.dpbs">http://www.sharethis.com/legal/privacy/#sthash.97W6NVSe.dpbs</a></div>\r\n\r\n<div> </div>\r\n\r\n<div>Si ricorda comunque agli utenti che tutti i moderni browser offrono la possibilita di controllare le impostazioni di privacy, anche per quello che riguarda l''uso dei cookie. Attenzione: non tutti i browser presenti su tablet o smartphone offrono lo stesso livello di controllo della privacy.</div>\r\n\r\n<div> </div>\r\n\r\n<div>Riportiamo qui di seguito le procedure per accedere a queste impostazioni per i browser più utilizzati:</div>\r\n\r\n<div> </div>\r\n\r\n<div>Chrome (<a href="https://support.google.com/chrome/answer/95647?hl=it">https://support.google.com/chrome/answer/95647?hl=it</a>)</div>\r\n\r\n<div>Internet explorer (<a href="http://windows.microsoft.com/it-it/windows-vista/block-or-allow-cookies">http://windows.microsoft.com/it-it/windows-vista/block-or-allow-cookies</a>)</div>\r\n\r\n<div>Firefox (<a href="https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences">https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences</a>)</div>\r\n\r\n<div>Opera (<a href="http://www.opera.com/help/tutorials/security/privacy/">http://www.opera.com/help/tutorials/security/privacy/</a>)</div>\r\n\r\n<div>Safari (<a href="https://support.apple.com/kb/PH17191?locale=en_US">https://support.apple.com/kb/PH17191?locale=en_US</a>)</div>', NULL, 0, 1, 0, 0, NULL, 0, NULL, NULL);

-- Structure query
ALTER TABLE `sys_conf` ADD `query_cache` TINYINT(1) NOT NULL DEFAULT '0', ADD `query_cache_time` SMALLINT(4) NULL;

ALTER TABLE `page_opt` CHANGE `newsletter_tpl_code` `newsletter_tpl_code` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query
INSERT INTO page_entry (category_id, author, creation_date, last_edit_date, title, slug, image, url_image, text, tags, enable_comments, published, social, private, users, [read], tpl_code, box_tpl_code) VALUES
(NULL, 1, '2015-05-11 15:05:21', '2015-05-12 12:40:08', 'Privacy - Cookie', 'privacy-cookie', NULL, NULL, '<div>In merito all''uso di cookie su questo sito, si dichiara che:</div>
<div> </div>
<div>1. Continuando la navigazione, si accettano implicitamente i cookie che il sistema installerà.</div>
<div> </div>
<div>2. Questo sito NON fa uso di cookie di PROFILAZIONE degli utenti.</div>
<div> </div>
<div>3. Questo sito NON consente l''invio di cookie di PROFILAZIONE di TERZE PARTI.</div>
<div> </div>
<div>4. Questo sito fa uso esclusivamente di cookie TECNICI per salvare i parametri di sessione e agevolare quindi la navigazione agli utenti. In particolare troverete il cookie [SITE]_SESSID che contiene il codice di sessione utilizzato per l''autenticazione e per la velocizzazione della navigazione.</div>
<div> </div>
<div>5. Potranno inoltre essere presenti cookie ESCLUSIVAMENTE TECNICI di terze parti:</div>
<div> </div>
<div>Google (Analitycs / Maps / reCAPTCHA)</div>
<div><a href="http://www.google.com/policies/technologies/types/" style="line-height: 21.6000003814697px;">http://www.google.com/policies/technologies/types/</a></div>
<div> </div>
<div>ShareThis</div>
<div><a href="http://www.sharethis.com/legal/privacy/#sthash.97W6NVSe.dpbs">http://www.sharethis.com/legal/privacy/#sthash.97W6NVSe.dpbs</a></div>
<div> </div>
<div>Si ricorda comunque agli utenti che tutti i moderni browser offrono la possibilita di controllare le impostazioni di privacy, anche per quello che riguarda l''uso dei cookie. Attenzione: non tutti i browser presenti su tablet o smartphone offrono lo stesso livello di controllo della privacy.</div>
<div> </div>
<div>Riportiamo qui di seguito le procedure per accedere a queste impostazioni per i browser più utilizzati:</div>
<div> </div>
<div>Chrome (<a href="https://support.google.com/chrome/answer/95647?hl=it">https://support.google.com/chrome/answer/95647?hl=it</a>)</div>
<div>Internet explorer (<a href="http://windows.microsoft.com/it-it/windows-vista/block-or-allow-cookies">http://windows.microsoft.com/it-it/windows-vista/block-or-allow-cookies</a>)</div>
<div>Firefox (<a href="https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences">https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences</a>)</div>
<div>Opera (<a href="http://www.opera.com/help/tutorials/security/privacy/">http://www.opera.com/help/tutorials/security/privacy/</a>)</div>
<div>Safari (<a href="https://support.apple.com/kb/PH17191?locale=en_US">https://support.apple.com/kb/PH17191?locale=en_US</a>)</div>', NULL, 0, 1, 0, 0, NULL, 0, NULL, NULL);

-- Structure query
ALTER TABLE sys_conf ADD query_cache TINYINT NOT NULL 
	CONSTRAINT DF_sys_conf_query_cache DEFAULT '0';
ALTER TABLE sys_conf ADD query_cache_time SMALLINT NULL;

ALTER TABLE page_opt ALTER COLUMN newsletter_tpl_code TEXT;

