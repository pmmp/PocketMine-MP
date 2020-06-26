## Introduction
화살(발사체)의 움직임이 사다리에 의하여 변경됩니다.
The arrow(projectile) motion being changed by the ladder

### Relevant issues

* Fixes #3602 

-->

## Changes
엔티티가 사다리 근처에 있을경우 사다리에 오른것(onGround)으로 판단되어
즉. 사다리에 엔티티가 걸린것으로 판단되어 땅으로 떨어집니다.
If the entity is near the ladder, it is judged to be on the ground.
That is, it falls to the ground because it is determined that the entity is caught in the ladder.

이것을 수정하기위해 발사체에 속한 엔티티는 예외로 수정하였습니다.
In order to correct this, entities that belong to projectiles have been modified as exceptions.

## Backwards compatibility
모든 발사체의 움직임은 사다리에 영향을 받지 않습니다.
All projectiles motion are not affected by the ladder 


Requires translations: Korean

| Name | Value in eng.ini |
| :--: | :---: |
| `foo.bar` | `Foo bar` |

-->

## Tests
<!--
Details should be provided of tests done. Simply saying "tested" or equivalent is not acceptable.

Attach scripts or actions to test this pull request, as well as the result
-->
