//-- convert svg to png
export async function svgToPng(svgString: string) {
    const blob = new Blob([svgString], {type: "image/svg+xml"})
    const url =  URL.createObjectURL(blob)

    const image = new Image();

    await new Promise((resolve, reject)=>{
        image.onload = resolve;
        image.onerror = reject;
        image.src = url;
    })

    const canvas = document.createElement("canvas")
    canvas.width = image.width;
    canvas.height = image.height;

    const ctx = canvas.getContext('2d')
    ctx?.drawImage(image, 0, 0)

    URL.revokeObjectURL(url);
    return canvas.toDataURL("image/png");
}


//-- Convert an object into a formdata
export function objectToFormData(
    obj: Record<string, any>, 
    form?: FormData, 
    transformMap?: Record<string, string>
) {
    const formData = form ?? new FormData();

    Object.entries(obj).forEach(([key, value]) => {
        if (value === null || value === undefined) return;
        
        const mkey = mapKey(key, transformMap); //-- transform key

        if (Array.isArray(value)) {
            value.forEach((item) => {

                if (item instanceof File || item instanceof Blob) {
                    formData.append(mkey, item);
                } 
                else if (typeof item === "object") {
                    formData.append(mkey, JSON.stringify(item));
                } 
                else {
                    formData.append(mkey, String(item).trim());
                }
            });
        } 
        else if (value instanceof File || value instanceof Blob) {
            formData.append(mkey, value);
        } 
        else if (typeof value === "object") {
            formData.append(mkey, JSON.stringify(value));
        } 
        else {
            formData.append(mkey, String(value).trim());
        }
    });

    return formData;
}





const mapKey = (key: string, transformMap?: Record<string, string>): string => {
    return transformMap?.[key] ?? key;
};



export const formatLocation = (location?: {
  street?: string;
  postalCode?: string;
  city?: string;
  country?: string;
}) => {
  if (!location) return "";

  const cityLine = [location.postalCode, location.city]
    .filter(Boolean)
    .join(" ");

  return [location.street, cityLine, location.country]
    .filter(Boolean)
    .join(", ");
};


/**
 * Recursively converts an object into a FormData instance.
 *
 * Nested objects are represented using bracket notation:
 * `company.name`
 * becomes `company[name]`.
 *
 * Arrays are represented using indexed bracket notation:
 * `images[0][file]`, `images[1][file]`, etc.
 *
 * File and Blob instances are appended directly without serialization.
 * FileList instances are converted into indexed file entries.
 *
 * Null and undefined values are ignored.
 * Primitive values are converted to strings.
 *
 * @param obj The object to convert.
 * @param form Optional existing FormData instance to append to.
 * @param transformMap Optional map used to transform property names.
 * @param parentKey Internal key used during recursive traversal.
 *
 * @returns The resulting FormData instance.
 */
export function objectToDeepFormData(
    obj: Record<string, unknown>,
    form?: FormData,
    transformMap?: Record<string, string>,
    parentKey?: string
): FormData {
    const formData = form ?? new FormData();

    const appendValue = (
        key: string,
        value: unknown
    ): void => {
        if (value === null || value === undefined) {
            return;
        }

        // Files and blobs must be appended directly.
        if (value instanceof File || value instanceof Blob) {
            formData.append(key, value);
            return;
        }

        // Handle FileList.
        if (value instanceof FileList) {
            Array.from(value).forEach((file, index) => {
                appendValue(`${key}[${index}]`, file);
            });

            return;
        }

        // Handle arrays.
        if (Array.isArray(value)) {
            value.forEach((item, index) => {
                appendValue(`${key}[${index}]`, item);
            });

            return;
        }

        // Recursively handle nested objects.
        if (typeof value === 'object') {
            Object.entries(value as Record<string, unknown>).forEach(
                ([childKey, childValue]) => {
                    const mappedChildKey = mapKey(
                        childKey,
                        transformMap
                    );

                    const nestedKey = `${key}[${mappedChildKey}]`;
                    appendValue(nestedKey, childValue);
                }
            );

            return;
        }

        // Handle primitive values.
        formData.append(key, String(value).trim());
    };

    Object.entries(obj).forEach(([key, value]) => {
        const mappedKey = mapKey(key, transformMap);
        const rootKey = parentKey
            ? `${parentKey}[${mappedKey}]`
            : mappedKey;

        appendValue(rootKey, value);
    });

    return formData;
}


