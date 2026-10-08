export type EnumOption<V> = { value: V; label: string };

// 数値のキーは昇順に列挙されるので、選択肢は値の順に並ぶ
export const toOptions = <V extends number>(names: Record<V, string>): EnumOption<V>[] =>
  Object.entries<string>(names).map(([value, label]) => ({ value: Number(value) as V, label }));
