# TODO

## Priorita 1 (kritické)
- [x] Opravit fallback CA certifikátu v src/FioApi/Transferrer.php.
  - Aktuální fallback odkazuje na neexistující soubor src/FioApi/keys/Geotrust_PCA_G3_Root.pem.
  - Cíl: odstranit neplatný fallback nebo přidat validní cert cestu tak, aby upload/download neselhal bez composer/ca-bundle.

## Priorita 2 (dokumentace a DX)
- [x] Sjednotit název balíčku v README.
  - V README.md je install příkaz pro mhujer/fio-api-php.
  - Cíl: uvést aktuální balíček z composer.json (webwingscz/fio-api-php) a zkontrolovat další historické odkazy.

## Priorita 3 (stabilita API)
- [x] Rozšířit error handling v src/FioApi/Download/Downloader.php.
  - Vedle HTTP 409/500 doplnit mapování pro timeout/connect chyby a parse chyby JSON.
  - Cíl: vracet predikovatelné doménové výjimky místo nízkoúrovňových chyb.

## Priorita 4 (odolnost provozu)
- [x] Přidat konfigurovatelné timeouty a retry strategii.
  - Cíl: umožnit bezpečné retry (exponenciální backoff) s ohledem na limit tokenu.

## Priorita 5 (test coverage)
- [x] Doplnit testy na chybové scénáře.
  - HTTP 409/500, timeout/connect exception, nevalidní JSON/XML odpověď.

## Priorita 6 (code quality)
- [x] Modernizovat coding standards.
  - Přechod z PSR-2 na PSR-12.
  - Aktualizovat squizlabs/php_codesniffer z fixní staré verze.

## Priorita 7 (konfigurovatelnost)
- [x] Umožnit konfiguraci base URL v src/FioApi/UrlBuilder.php.
  - Cíl: podpora test/sandbox/proxy scénářů bez zásahu do kódu knihovny.

## Priorita 8 (release management)
- [x] Přidat do README sekci o stabilitě a kompatibilitě.
  - Podporované verze PHP/Guzzle, BC pravidla, release/tag politika.
