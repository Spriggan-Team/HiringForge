
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


//-- Convert or object ointo a Formdata
export  function objectToFormData(obj: Record<string, any>, form?: FormData){
    const formData = form ?? new FormData();

    Object.entries(obj).forEach(([key, value])=>{
        if(value === null || value === undefined) return;

        if(Array.isArray(value))
        {
            value.forEach((item) => {
                if(item instanceof File || item  instanceof Blob){
                    formData.append(key, item);
                }
                else if(typeof item === "object"){
                    formData.append(key, JSON.stringify(item));
                }
                else{
                    formData.append(key, String(item));
                }
            })
        }
        else if(value instanceof File || value instanceof Blob){
            formData.append(key, value)
        }
        else if(typeof value === "object"){
            formData.append(key, JSON.stringify(value))
        }
        else {
            formData.append(key, String(value))
        }
    })

    return formData;
}