
export function isHtml(str: string){
    const parser = new DOMParser();
    const doc = parser.parseFromString(str, "text/html");

    return Array.from(doc.body.childNodes).some(
        node => node.nodeType === Node.ELEMENT_NODE
    )
}


export const openHtml = (body: string) => {
  const blob = new Blob([body], { type: "text/html;charset=utf-8" });
  const blobUrl = URL.createObjectURL(blob);

  const newWindow = window.open(blobUrl, "_blank");

  if (!newWindow) {
    console.warn("Pop-up bloqué ! Redirection vers la page d'erreur...");
    alert("Le navigateur a bloqué le pop-up d'erreur HTML. Autorisez les pop-ups pour localhost.");
  }
};



export default { openHtml, isHtml }