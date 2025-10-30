# PocketMine-Language

These files contain translation strings used in PocketMine-MP.

## Contributing translations (non-English)
To contribute translations, please use the [Crowdin Translation Page](http://translate.pocketmine.net/), select the language you want to translate and go to PocketMine-MP and select "PocketMine core strings". There may be multiple branches available - if in doubt, stick with `stable`.

## For maintainers
### Adding new strings
> [!CAUTION]
> Only `eng.ini` should be modified directly.
>
> Do not modify any of the other languages manually. They are managed by Crowdin, and any changes you make to them will be overwritten.

To add new strings, add them ONLY to `eng.ini`.

- Vanilla strings should use the same keys as used by [Mojang](https://raw.githubusercontent.com/Mojang/bedrock-samples/refs/heads/main/resource_pack/texts/en_US.lang). However, PocketMine-MP currently uses `{%paramName}` for parameters instead of `%1$s` `%2$s` etc, so you can't copy-paste them directly. Make sure to adapt these.
- Strings specifically for PocketMine-MP can have any keys you like, but they must start with `pocketmine.`

> [!TIP]
> You don't need to worry about translating newly added strings into other languages.
> Just make sure that `eng.ini` is correct, and translators will handle other languages via Crowdin.

Once you update `eng.ini`, run `composer update-codegen` to regenerate `KnownTranslationFactory` et al.
This will generate a function that you can use to create a parameterised `Translatable` instance for the string.

Crowdin will synchronize `eng.ini` every time it's updated on `stable`, `minor-next` and `major-next` to put up new strings for translation.

### Pitfalls
- If you have issues with translation files being deleted, add a language mapping in the Crowdin config. Some issues arose with Chinese due to Chinese Simplified and Chinese Traditional both mapping to `zho`, requiring a mapping to `zho-cn` for simplified.
