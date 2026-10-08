import { getDoc, type DecoratorContext, type Enum } from "@typespec/compiler";
import { setExtension } from "@typespec/openapi";

/**
 * enum の各メンバーの @doc を集め、OAS の x-enum-descriptions として出力する
 * 配列の並びはメンバーの宣言順で、OAS の enum の並びと一致する
 */
function $enumDescriptions(context: DecoratorContext, target: Enum): void {
  const descriptions: string[] = [];

  for (const member of target.members.values()) {
    const doc = getDoc(context.program, member);

    if (doc === undefined) {
      context.program.reportDiagnostic({
        code: "enum-member-doc-missing",
        severity: "error",
        message: `@enumDescriptions を付けた enum のメンバー ${target.name}.${member.name} に @doc がありません`,
        target: member,
      });
      continue;
    }

    descriptions.push(doc);
  }

  setExtension(context.program, target, "x-enum-descriptions", descriptions);
}

export const $decorators = {
  "IsekaiObservatory.Shared": {
    enumDescriptions: $enumDescriptions,
  },
};
