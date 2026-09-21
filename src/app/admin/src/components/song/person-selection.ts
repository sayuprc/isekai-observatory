import type { RequestSongPerson, SongPersonRole } from '../../generated';

export type SelectedPerson = {
  personId: string;
  name: string;
};

export type PersonSelections = Record<SongPersonRole, SelectedPerson[]>;

export const addSelectedPerson = (selected: SelectedPerson[], person: SelectedPerson): SelectedPerson[] => {
  if (selected.some(item => item.personId === person.personId)) {
    return selected;
  }

  return [...selected, person];
};

export const addSelectedPersonToRole = (
  selections: PersonSelections,
  role: SongPersonRole,
  person: SelectedPerson,
): PersonSelections => ({
  ...selections,
  [role]: addSelectedPerson(selections[role], person),
});

export const toRequestSongPersons = (
  selected: SelectedPerson[],
  role: SongPersonRole,
): RequestSongPerson[] => selected.map((person, index) => ({
  personId: person.personId,
  role,
  orderNo: index + 1,
}));
