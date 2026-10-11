import { createSignal } from 'solid-js';

export const createSubmitting = () => {
  const [isSubmitting, setIsSubmitting] = createSignal(false);

  const withSubmitting = <T extends unknown[], R>(handler: (...args: T) => Promise<R>) => {
    return async (...args: T): Promise<R | undefined> => {
      if (isSubmitting()) {
        return;
      }

      setIsSubmitting(true);
      try {
        return await handler(...args);
      } finally {
        setIsSubmitting(false);
      }
    };
  };

  return { isSubmitting, withSubmitting };
};
