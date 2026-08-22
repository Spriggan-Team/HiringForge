

export function intercept<
    T extends object,
    TResult = unknown
>(
    target: T,
    before?: (method: keyof T, args: unknown[]) => void,
    after?: (
        method: keyof T,
        result: TResult | Error
    ) => void
): T {

    return new Proxy(target, {
        get(obj, prop, receiver) {
            const value = Reflect.get(obj, prop, receiver);

            if (typeof value !== "function")
                return value;

            return (...args: unknown[]) => {
                before?.(prop as keyof T, args);

                try {
                    const result = Reflect.apply(value, obj, args);

                    if (result instanceof Promise) {
                        return result
                            .then((data: TResult) => {
                                after?.(prop as keyof T, data);
                                return data;
                            })
                            .catch((error: Error) => {
                                after?.(prop as keyof T, error);
                                throw error;
                            });
                    }

                    after?.(prop as keyof T, result);
                    return result;

                } catch (error) {
                    after?.(prop as keyof T, error as Error);
                    throw error;
                }
            };
        }
    });
}