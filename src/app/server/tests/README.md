# テスト戦略

3 つの階層でテストをする

1. Unit
2. Integration
3. Feature

## Unit

### テスト対象

- Domain 層
- UseCase 層

### 内容

依存関係は Mock を使う  
想定するケースをできるだけテストする

## Integration

### テスト対象

- Domain 層
- UseCase 層
- Infrastructure 層

※I/O(DB や外部 API など)があるもののみを対象とする

### 内容

依存関係はできるだけ本物を使う  
基本的にハッピーパスのみをテストする

## Feature

### テスト対象

- Infrastructure 層

### 内容

依存関係はできるだけ本物を使う  
ユーザーの挙動に沿ったケースをテストする
