import { ALLOWED_STATUS_TRANSITIONS, type ApplicationStatusValue } from "./application";


export function canTransitionStatus(
  currentStatus: ApplicationStatusValue,
  newStatus: ApplicationStatusValue
): boolean {
  if (currentStatus === newStatus) {
    return true; // No further processing
  }

  const allowedNextStatuses = ALLOWED_STATUS_TRANSITIONS[currentStatus];
  return allowedNextStatuses.includes(newStatus);
}