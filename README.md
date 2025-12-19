## 概要
よもぎサーバ用に一部ブロック処理を変更し、軽量化を行うことを目的として作られたPocketMine-MP派生のリポジトリです。

## 背景
PocketMine-MP v5.0以降、ブロック システムが大幅に再設計され、プラグインレベルでの既存ブロック動作の上書きが困難になりました。
v4まで可能だった `Block` クラスの継承による動作変更が、以下の理由により実質的に不可能となっています：

- `RuntimeBlockStateRegistry` への登録が複雑化
- `BlockStateToObjectDeserializer` の手動設定が必要
- `VanillaBlocks` クラスの内部構造変更
- リフレクションを用いた内部状態変更の不安定性
- プライベートAPIの頻繁な変更によるメンテナンス困難

開発者自身も「disastrously complex and painful to maintain」と認めており、現在は公式にサポートされていません。

## 解決アプローチ
プラグインレベルでの制限を回避するため、PocketMine-MPのソースコード自体を直接修正し、必要な機能を組み込んだカスタムビルドを作成しています。

## ブランチ戦略

### ブランチ構成
```
stable (アップストリーム同期専用)
└── upstream pmmp/PocketMine-MP tracking
```

### ブランチの役割
- **customize**: 統合・リリース用メインブランチ。全ての機能がマージされ、自動ビルドが実行される
- **stable**: 公式 `pmmp/PocketMine-MP` の最新版を追跡する同期専用ブランチ
- **customize/feature/***: 個別機能の開発ブランチ

### 開発者向け
```bash
# リポジトリクローン
git clone https://github.com/YOUR_USERNAME/PocketMine-MP.git
cd PocketMine-MP

# アップストリーム設定
git remote add upstream https://github.com/pmmp/PocketMine-MP.git
```

### 手動ビルド
```bash
# 本番用ビルド
composer install --no-dev --classmap-authoritative
composer make-server --out CustomPocketMine.phar

# 開発用クイックビルド
./scripts/quick-build.sh
```
