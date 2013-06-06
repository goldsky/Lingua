Always uncache your lexicon tags, and then add snippet ([[!lingua.cultureKey]])
to get the current language:

[[!%foo.bar? &namespace=`sky` &language=`[[!lingua.cultureKey]]`]]

After installation, please make sure the Lingua's plugin has "OnHandleRequest"
checked.

References:
1. http://en.wikipedia.org/wiki/ISO_639-1_language_matrix
2. http://www.science.co.il/language/locale-codes.asp
3. http://www.php.net/manual/en/book.intl.php