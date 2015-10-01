# TODO

This branch will get used to to fix all issues with items and blocks.  
Working from branch mcpe-0.12 tested with MCPE 0.12.1

## src/pocketmine/item/Item.php

- [x] add missing items
    - [x] sort all items in ``public static function init(){}``
        - sort using ID
    - [x] create list of missing items
- [ ] create seperate functions for views (maybe?)
    - [ ] sort everything in ``private static function initCreativeItems(){}``
        - the way it looks ingame using **MCPE 0.12.1**  

## src/pocketmine/block/Block.php

- [ ] add missing blocks
    - [ ] sort all blocks in init()
        - sort using ID
    - [ ] create a list of missing blocks
