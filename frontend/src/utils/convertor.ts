
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