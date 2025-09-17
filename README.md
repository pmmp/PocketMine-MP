## 概要
よもぎサーバ用の一部ブロック処理などを行うPocketMine-MP派生のリポジトリです。

## 背景
PocketMine-MP v5.0以降、ブロック システムが大幅に再設計され、プラグインレベルでの既存ブロック動作の上書きが困難になりました。v4まで可能だった `Block` クラスの継承による動作変更が、以下の理由により実質的に不可能となっています：

### PocketMine-MP v5の制限事項
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

### 開発フロー
1. **機能開発**: `customize/feature/機能名` ブランチで実装
2. **プルリクエスト**: `customize` ブランチへのPR作成
3. **コードレビュー**: 変更内容の確認とテスト
4. **マージ**: `customize` ブランチへのマージでCI自動実行
5. **リリース**: 自動ビルドされたPharファイルの配布

### アップストリーム同期
- 週次での自動同期チェック
- `stable` ブランチへの最新版取り込み
- 競合解決後の `customize` ブランチへのマージ

## セットアップ

### 開発者向け
```bash
# リポジトリクローン
git clone https://github.com/YOUR_USERNAME/PocketMine-MP.git
cd PocketMine-MP

# アップストリーム設定
git remote add upstream https://github.com/pmmp/PocketMine-MP.git
```

## ビルドシステム

### 自動ビルド
- **トリガー**: `customize` ブランチへのpush
- **出力**: GitHub Releases への自動配布
- **ファイル名形式**: `YomogiPocketMine-YYYYMMDD-HHMMSS-コミットハッシュ.phar`

### 手動ビルド
```bash
# 本番用ビルド
composer install --no-dev --classmap-authoritative
composer make-server --out CustomPocketMine.phar

# 開発用クイックビルド
./scripts/quick-build.sh
```

## 継続的インテグレーション

### GitHub Actions
- **ビルドテスト**: PHP 8.3でのみ動作確認
- **セキュリティ監査**: Composer audit実行
- **静的解析**: PHPStan（設定されている場合）
- **アップストリーム同期**: 週次での自動チェック

## 注意事項

### 互換性
- 公式PocketMine-MPプラグインとの完全互換性は保証されません
- カスタム変更により一部プラグインが正常動作しない可能性があります

## ライセンス
元のPocketMine-MPのLGPL-3.0ライセンスに従います。