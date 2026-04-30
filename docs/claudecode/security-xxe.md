# [XXE] XML Manipülasyon ve XXE Kuralları

## [XXE-01] External Entity İşleme Devre Dışı — KRİTİK
Tüm XML parser'larda DTD işleme ve external entity çözümleme kapatılmalıdır.

```
✗ YANLIŞ:
  // PHP: simplexml_load_string($xml)           → varsayılanda XXE açık
  // .NET: new XmlDocument()                     → eski sürümlerde XXE açık
  // JS:   Varsayılan XML parser                 → kontrol edilmeli

✓ DOĞRU:
  // PHP: libxml_disable_entity_loader(true)     → PHP < 8.0
  // .NET: XmlReaderSettings { DtdProcessing = DtdProcessing.Prohibit }
  // Genel: DTD processing = disabled, external entities = disabled
```

Kontrol:
- [ ] Tüm XML parser'larda DTD processing devre dışı
- [ ] External entity resolution kapalı
- [ ] XInclude devre dışı
- [ ] SVG dosya yükleme varsa XXE kontrolü yapılıyor

## [XXE-02] XML Bomb (Billion Laughs) Koruması — YÜKSEK
Entity expansion sınırlandırılmalı, maksimum XML gövde boyutu belirlenmelidir.

```
✗ YANLIŞ:
  <!DOCTYPE foo [<!ENTITY xxe "xxexxexxe...">]>
  <data>&xxe;&xxe;&xxe;...</data>   → bellek tükenmesi

✓ DOĞRU:
  parser.setEntityExpansionLimit(100)
  if (xmlSize > MAX_XML_SIZE) reject()
```

## [XXE-03] SOAP / XML-RPC Güvenliği — YÜKSEK
SOAP kullanılıyorsa WSDL yerel kopyası kullanılmalı, harici URL'den dinamik yüklenmemelidir. WS-Security ve XML imza doğrulaması uygulanmalıdır.

```
✗ YANLIŞ:
  client = new SoapClient(userProvidedWsdlUrl)

✓ DOĞRU:
  client = new SoapClient('/config/service.wsdl', [options])
```
