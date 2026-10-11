import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Viewer;

model EventListResponse {
  events: Event[];
  nextCursor?: cursor;
}
