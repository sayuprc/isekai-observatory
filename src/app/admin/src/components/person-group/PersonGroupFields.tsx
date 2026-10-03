import { Show } from 'solid-js';
import type { MemberForm } from './member-form';
import { MemberEditor } from './MemberEditor';

interface PersonGroupFieldsProps {
  name: string;
  onNameChange: (name: string) => void;
  members: MemberForm[];
  onMembersChange: (updater: (prev: MemberForm[]) => MemberForm[]) => void;
  nameError?: string;
}

// 作成と編集で共通の入力欄
export const PersonGroupFields = (props: PersonGroupFieldsProps) => (
  <>
    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
      <label class="label">グループ名</label>
      <input
        type="text"
        class="input w-full"
        name="name"
        required
        value={props.name}
        onInput={(e) => props.onNameChange(e.currentTarget.value)}
        classList={{ 'input-error': !!props.nameError }}
      />
      <Show when={props.nameError}>{(message) => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
    </fieldset>
    <MemberEditor members={props.members} onChange={props.onMembersChange} />
  </>
);
