namespace IsekaiObservatory.Public;

@doc("次ページを取得するためのトークン。中身の形式は保証しない")
@friendlyName("pageToken")
scalar pageToken extends string;

@doc("1 ページあたりの件数")
@minValue(1)
@maxValue(100)
@friendlyName("pageSize")
scalar pageSize extends int32;
