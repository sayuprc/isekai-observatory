namespace IsekaiObservatory.Shared;

@doc("UUID v4")
@format("uuid")
@friendlyName("uuid")
scalar uuid extends string;

@doc("メールアドレス")
@format("email")
@friendlyName("email")
scalar email extends string;

@doc("表示順")
@minValue(1)
@friendlyName("orderNo")
scalar orderNo extends int32;

@doc("ページ番号")
@minValue(1)
@friendlyName("page")
scalar page extends int32;

@doc("ソート順")
@friendlyName("SortOrder")
@enumExtensions
enum SortOrder {
  @doc("昇順")
  asc: "asc",

  @doc("降順")
  desc: "desc",
}

@doc("1ページあたりの件数")
@friendlyName("PerPage")
@enumExtensions
enum PerPage {
  @doc("25 件")
  twentyFive: 25,

  @doc("50 件")
  fifty: 50,

  @doc("100 件")
  oneHundred: 100,
}

@doc("次ページを記すキー")
@friendlyName("cursor")
scalar cursor extends string;

@doc("1 度に取得する件数")
@minValue(1)
@maxValue(50)
@friendlyName("limit")
scalar limit extends int32;
