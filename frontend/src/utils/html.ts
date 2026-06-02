
export function isHtml(str: string){
    const parser = new DOMParser();
    const doc = parser.parseFromString(str, "text/html");

    return Array.from(doc.body.childNodes).some(
        node => node.nodeType === Node.ELEMENT_NODE
    )
}

export const openHtml =(body: string)=>{
    //--- open the page
    const newWindow = window.open("", "_blank");
    if (newWindow) {
        newWindow.document.open();
        newWindow.document.write(body);
        newWindow.document.close();
    }
}

export default { openHtml, isHtml }