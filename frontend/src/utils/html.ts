
export function isHtml(str: string){
    const parser = new DOMParser();
    const doc = parser.parseFromString(str, "text/html");

    return Array.from(doc.body.childNodes).some(
        node => node.nodeType === Node.ELEMENT_NODE
    )
}