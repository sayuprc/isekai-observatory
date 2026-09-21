import { describe, expect, test } from "bun:test";
import { fixEnumTypes } from "./fix-enum-types";

describe("fixEnumTypes", () => {
  test("converts type: number followed by enum: to type: integer with format: int32", () => {
    const input = `    SomeValue:
      type: number
      enum:
        - 1
        - 2
`;
    const expected = `    SomeValue:
      type: integer
      format: int32
      enum:
        - 1
        - 2
`;
    expect(fixEnumTypes(input)).toBe(expected);
  });

  test("does not modify type: number that is not followed by enum:", () => {
    const input = `    SomeValue:
      type: number
      description: a plain number
`;
    expect(fixEnumTypes(input)).toBe(input);
  });

  test("does not modify type: string enum:", () => {
    const input = `    SomeValue:
      type: string
      enum:
        - foo
        - bar
`;
    expect(fixEnumTypes(input)).toBe(input);
  });

  test("handles multiple numeric enums in one file", () => {
    const input = `    RoleValue:
      type: number
      enum:
        - 1
        - 2
    SongTypeValue:
      type: number
      enum:
        - 1
        - 2
        - 3
`;
    const result = fixEnumTypes(input);
    expect(result).not.toContain("type: number");
    expect(result.match(/type: integer/g)).toHaveLength(2);
    expect(result.match(/format: int32/g)).toHaveLength(2);
  });

  test("preserves indentation level", () => {
    const input = `        DeepValue:
          type: number
          enum:
            - 1
`;
    const result = fixEnumTypes(input);
    expect(result).toContain("          type: integer");
    expect(result).toContain("          format: int32");
    expect(result).toContain("          enum:");
  });
});
