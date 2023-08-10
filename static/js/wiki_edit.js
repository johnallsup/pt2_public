// TODO -- if we want to make auth stuff optional, we have scope issues.
// we want a namespace, say winodw.PT where we store everything
// then we have window.PT = new PT(); window.addEventListener("load",PT.load())
// then PT adds a new class PT.editor_auth(this) which
// then adds shortcuts via PT.add_shortcut("C-s",)
class PTUIEdit extends PTUI {
  constructor(ajax) {
    super()
    this.ajax = ajax
    this.editor = new PTEditor(this,q("textarea.editor"))
    this.dirty = false
    window.addEventListener("focus",e => this.handleFocus(e))
  }
  handleFocus(e) {
    this.editor.focus()
  }
  setupUI() {
    super.setupUI()
    const { q, qq } = window
    function c(sel,cb) {
      qq(sel).forEach(elt => {
        elt.addEventListener("click",e => {
          e.preventDefault()
          cb(e)
        })
      })
    }
    c(".action.duplicate", e => this.duplicateView())
    c(".action.duplicate-edit",e => this.duplicateEdit())
    c(".action.versions",e => this.showVersions() )
    c(".action.abort", e => this.returnToView())
    c(".action.show-preview", e => this.showPreview())
    c(".action.save", e => this.save(false))
    c(".action.hamburger", e => this.toggleOptionsBar("hamburger"))
    c(".action.more-options", e => this.toggleOptionsBar("more-options"))
    c(".action.editor-normal-font",e => this.editor.setFontSize("normal"))
    c(".action.editor-large-font",e => this.editor.setFontSize("large"))
    c(".action.editor-huge-font",e => this.editor.setFontSize("huge"))
    c(".action.mo-leftarrow", e => this.editor.moveLeft())
    c(".action.mo-rightarrow", e => this.editor.moveRight())
    c(".action.mo-prevheader", e => this.editor.movePrevHeader())
    c(".action.mo-nextheader", e => this.editor.moveNextHeader())
    c(".action.mo-prevline", e => this.editor.prevLine())
    c(".action.mo-nextline", e => this.editor.nextLine())
  }
  showPreview() {
    let { local: path } = this.getUriInfo()
    let source = this.editor.source()
    this.ajax.preview(path,source,e => this.didGetPreview(e),e => this.failedToGetPreview(e))
  }
  didGetPreview(e) {
    console.log("Preview",e)
    let { rendered: htmlSource } = e
    let div = document.createElement("div")
    div.classList.add("rendered-preview")
    div.innerHTML = htmlSource
    this.previewBox.showPreview(window.pageName,window.pagePath,div)
  }
  failedToGetPreview(e) {
    this.errorBox.showContent("Failed to get preview")
    console.log("1234 Failed to get preview",e)
  }
  showVersions() {
    // show in a dialog
    let { local: path } = this.getUriInfo()
    console.log("showVersions",{path})
    this.ajax.versions(path,e => this.didGetVersions(e),e => this.failedToGetVersions(e))
  }
  didGetVersions(e) {
    console.log("Got versions",e)
    let path = e.path.replace(/\.ptmd$/,"")
    let pagename = path.replace(/^.*\//,"")
    this.versionsBox.showVersions(pagename,path,e.versions)
    //this.infoBox.showContent(div)
  }
  failedToGetVersions(e) {
    console.log("Failed to get versions 183",e)
    let div = document.createElement("div")
    let h1 = document.createElement("h1")
    h1.textContent = "Failed to get versions"
    div.append(h1)
    let pre = document.createElement("pre")
    pre.textContent = e.responseText
    div.append(pre)
    this.errorBox.showContent(div)
  }
  setDirty() {
    this.dirty = true
    document.body.classList.add("dirty")
  }
  clearDirty() {
    this.dirty = false
    document.body.classList.remove("dirty")
  }
  unimplemented(e) {
    console.warn("Unimplemented",e)
  }
  toggleOptionsBar(barName) {
    console.log("toggle options",barName)
    let currentOptionsBar = document.body.getAttribute("options-bar")
    if( currentOptionsBar === barName ) {
      document.body.removeAttribute("options-bar")
    } else {
      document.body.setAttribute("options-bar",barName)
    }
  }
  returnToView() {
    let { pagename } = this.getUriInfo()
    window.location.href = pagename
  }
  setupKeys() {
    super.setupKeys()
    const f = (t,d,h) => this.keys.addfp(t,d,h)
    const n = (t,d,h) => this.keys.addnp(t,d,h)

    f("C-`","save and return to view",e => this.save(true))
    f("C-s","save", e => this.save(false))
    f("C-S-a","abort edit and return to view", e => this.returnToView())
    f("C-3","toggle textarea-only", e => document.body.classList.toggle("textarea-only"))
    f("C-p","show preview",e => this.showPreview())

  }
  save(returnToView=false) {
    let source = this.editor.source()
    let uriInfo = this.getUriInfo()
    let { local:path } = uriInfo
    this.ajax.store(path, source, e => this.didSave(e,returnToView), e => this.failedToSave(e))
  }
  failedToSave(e) {
    console.log(`Failed to save`,e)
    const { responseText } = e
    let content = document.createElement("div")
    let h1 = document.createElement("h1")
    h1.textContent = "Failed to Save"
    content.append(h1)
    let pre = document.createElement("pre")
    pre.textContent = responseText
    content.append(pre)
    this.errorBox.showContent(content)
  }
  didSave(e,returnToView=false) {
    const { message, mtime_fmt_short } = e
    console.log("didSave: Save success",{mtime_fmt_short,message,e})
    qq("header .time").forEach(x => {
      x.textContent = mtime_fmt_short
    })

      /*
    let content = document.createElement("div")
    let h1 = document.createElement("h1")
    h1.textContent = "Saved Successfully"
    content.append(h1)
    let pre = document.createElement("pre")
    pre.textContent = responseText
    content.append(pre)
    this.infoBox.showContent(content)
    */
    this.clearDirty()
    if( returnToView ) {
      this.returnToView()
    }
  }
}

class PTEditor {
  constructor(ui,elt,options = {}) {
    this.ui = ui
    this.elt = elt
    this.options = {
      tabStr: "    ",
      ...options
    }
    if( ! this.elt ) throw new Error("No editor element")
    this.keys = new KeyHandler()
    this.elt.addEventListener("keydown",e => this.handleKey(e))
    this.elt.addEventListener("input",e => this.ui.setDirty())
    this.setupEditor()
    this.setupKeys()
  }
  handleKey(event) {
    return this.keys.handle(event)
  }
  source() {
    return this.elt.value
  }
  focus(e) {
    setTimeout(_ => this.elt.focus(),10)
  }
  setupEditor() {
    /*
     * garish colours to see if this function was executed.
     */
    //this.elt.style.border = "5px solid yellow"
    //this.elt.style.backgroundColor = "black"
    //this.elt.style.color = "white"
  }
  setupKeys() {
    const f = (t,d,h) => this.keys.addfp(t,d,h)
    const fn = (t,d,h) => this.keys.addf(t,d,h)
    const n = (t,d,h) => this.keys.addnp(t,d,h)

    f("tab","insert spaces",e => this.modifyTextInSelection(
      selection => ({ before: this.options.tabStr, selection, after: "" })))
    f("S-tab","shift left spaces",e => this.shiftLeft())
    f("C-f","say fuck",e => this.modifyTextInSelection(
      selection => ({ before: "fuck", selection: "shit", after: "bugger" })))
    f("C-enter","start new line below",e => this.insertNewLineBelow())
    f('C-S-2',"tab size = 2",e => {
      this.options.tabStr = "  "
      let s = "&nbsp;".repeat(2)
      this.ui.infoBox.showHtml(`Tab string now "<code>${s}</code>" (${this.options.tabStr.length} chars)`,500)
    })
    f('C-S-3',"tab size = 3",e => {
      this.options.tabStr = "  "
      let s = "&nbsp;".repeat(3)
      this.ui.infoBox.showHtml(`Tab string now "<code>${s}</code>" (${this.options.tabStr.length} chars)`,500)
    })
    f('C-S-4',"tab size = 4",e => {
      this.options.tabStr = "    "
      let s = "&nbsp;".repeat(4)
      this.ui.infoBox.showHtml(`Tab string now "<code>${s}</code>" (${this.options.tabStr.length} chars)`,500)
    })

    // key: $ is cursor pos, # is paste, s is selection
    //      $(...) means ... is the new selection
    // C-S-l [$](s)
    // A-C-l [[s]]$
    // C-A-v Paste link: sel!="" => s [[#]]$ ; else [[#]]$
    // C-S-v Paste link: sel1="" => [s](#)$ ; else [$](#)
    fn("C-S-v","paste link []()",e => this.pasteLink1(e))
    fn("A-C-v","paste link [[]]",e => this.pasteLink2(e))
    f("C-S-l","linkify selection []()",e => this.linkifySelection1())
    f("A-C-l","linkify selection [[]]",e => this.linkifySelection2())
    f("C-space","skip to end of link",e => this.skipToEndOfLink())
    f("C-S-space","skip to start of link",e => this.skipBackToStartOfLink())
    // we want editor to compile its help, and the help compiler
    // for the ui will take editor's help and prepend it to the main.

    // skip forward -- links headers blocks paras
    // skip backward -- links headers blocks paras
    // consider having a skip mode. So we enter skip mode, and then
    // have all keys available. Thus we need to have a non-default keys,
    // and a common keys. Esc exits skip mode. When in skip mode, change
    // the background-colour of the textarea
    // 
    // skip backwards to header:
    // i = selectionStart
    // left = text.substr(0,i)
    // j = left.lastIndexOf("\n#")
    // pos = j+1 // this will also work if index returns -1
    // selectionStart = selectionEnd = pos
    //
    // skip forwards to header
    // i = selectionEnd
    // right = text.substr(i)
    // j = right.indexOf("^#")
    // if( j == -1 ) j = text.length
    // selectionStart = selectionEnd = j
    //
    // note that this will find lines beginning with # in code blocks.
    // a problem with python

  }
  skipBackToStartOfLink() {
    const elt = this.elt
    const text = elt.value
    const a = elt.selectionStart;
    const b = elt.selectionEnd;
    let left = text.substring(0,b)
    while(left.length > 0 && (left[left.length-1] === "[" || left[left.length-1] === "(")) {
      left = left.substr(0,left.length-1)
    }
    if( left.length > 0 ) {
      let s = left.lastIndexOf("[")
      let r = left.lastIndexOf("(")
      let i = s > r ? s : r
      if( i >= 0 ) {
        elt.selectionStart = elt.selectionEnd = i+1
      }
    }
  }
  skipToEndOfLink() {
    const elt = this.elt
    const text = elt.value
    const a = elt.selectionStart;
    const b = elt.selectionEnd;
    const currentSelection = text.substring(a,b)
    const currentBefore = text.substring(0,a)
    const currentAfter = text.substring(b)
    let startPos = b

    // Naive and fragile, but ok.
    // search forward for first ]. If ](, search forward
    // from that point for next ). If ]], target is end of ]]
    let ca = currentAfter
    let i = ca.indexOf("]")
    if( i === -1 ) {
      return
    }
    if( ca[i+1] === "(" ) {
      let j = ca.substr(i+1).indexOf(")")
      if( j >= 0 ) {
        elt.selectionStart = elt.selectionEnd = b + i + 2 + j
      }
    } else if( ca[i+1] === "]" ) {
      elt.selectionStart = elt.selectionEnd = b + i + 2
    }
  }
  pasteLink1(e) {
    if(!navigator.clipboard) {
      console.log("pasteLink1 Can't access clipboard")
      return true // TODO test
    }
    e.preventDefault()
    console.log("pasteLink1")    
    navigator.clipboard.readText()
      .then(paste => {
        this.modifyTextInSelection((currentSelection,currentBefore,currentAfter) => {
          if( currentSelection.length > 0 ) {
            return {
              before: `[${currentSelection}](${paste})`,
              selection: "",
              after: ""
            }
          } else {
            return {
              before: `[`,
              selection: ``,
              after: `](${paste})`
            }
          }
        })
      })
      .catch(error => {
        // TODO error
        const msg = "Failed to read clipboard contents"
        console.log({msg,error})
      })
    return true
  }
  pasteLink2(e) {
    if(!navigator.clipboard) {
      console.log("pasteLink2 Can't access clipboard")
      return true // TODO test
    }
    e.preventDefault()
    console.log("pasteLink2")    
    navigator.clipboard.readText()
      .then(paste => {
        this.modifyTextInSelection((currentSelection,currentBefore,currentAfter) => {
          if( currentSelection.length > 0 ) {
            return {
              before: `${currentSelection} [[${paste}]]`,
              selection: "",
              after: ""
            }
          } else {
            return {
              before: `[[${paste}]]`,
              selection: ``,
              after: ``
            }
          }
        })
      })
      .catch(error => {
        // TODO error
        const msg = "Failed to read clipboard contents"
        console.log({msg,error})
      })
    return true
  }
  linkifySelection1() {
    // []()
    console.log("linkifySel1")    
    this.modifyTextInSelection((currentSelection,currentBefore,currentAfter) => {
      if( currentSelection.length > 0 ) {
        return {
          before: `[`,
          selection: "",
          after: `](${currentSelection})` 
        }
      } else {
        return {
          before: `[`,
          selection: ``,
          after: `]()`
        }
      }
    })
  }
  linkifySelection2() {
    // [[]]
    console.log("linkifySel2")    
    this.modifyTextInSelection((currentSelection,currentBefore,currentAfter) => {
      if( currentSelection.length > 0 ) {
        return {
          before: `[[${currentSelection}]] `,
          selection: "",
          after: ""
        }
      } else {
        return {
          before: `[[`,
          selection: ``,
          after: `]]`
        }
      }
    })
  }
  shiftLeft() {
    let elt = this.elt
    let text = elt.value
    let a = elt.selectionStart;
    let b = elt.selectionEnd;
    a = text.substr(0,a).lastIndexOf("\n")
    if( a == -1 ) {
      a = 0;
    }
    let left = text.substr(0,a)
    let right = text.substr(b)
    let selection = text.substr(a,b-a)
    let re = new RegExp(`^${this.options['tabStr']}`,"mg")
    selection = selection.replace(re,"")
    elt.value = left + selection + right
    elt.selectionStart = left.length
    elt.selectionEnd = left.length + selection.length
  }
  /**
   *
   * @param: callback(current,before,after)
   *         returns { before, selection, after }
   *         current is replaced with before+selection+after
   *         and selection becomes new selection
   */
  modifyTextInSelection(callback) {
    const elt = this.elt
    const text = elt.value
    console.log(1235,{elt,text})
    const a = elt.selectionStart;
    const b = elt.selectionEnd;
    const currentSelection = text.substring(a,b)
    const currentBefore = text.substring(0,a)
    const currentAfter = text.substring(b)
    const replacement = callback(currentSelection,currentBefore,currentAfter)
    const { before, selection, after } = replacement
    console.log(1236,{before,selection,after},replacement)
    const newText = currentBefore + before + selection + after + currentAfter
    elt.value = newText
    elt.selectionStart = currentBefore.length + before.length
    elt.selectionEnd = currentBefore.length + before.length + selection.length
  }
  insertNewLineBelow() {
    const textarea = this.elt
    const selectionEnd = textarea.selectionEnd
    const text = textarea.value
    const rightOfSelection = text.substr(selectionEnd)
    const nextNewLine = rightOfSelection.indexOf("\n")
    if( nextNewLine === -1 ) {
      // no newlines after selection,
      // so put new line right at the end
      const newText = text + "\n"
      textarea.value = newText
      textarea.selectionStart = newText.length
      textarea.selectionEnd = newText.length 
      textarea.scrollTop = textarea.scrollHeight
    } else {
      // split textarea.value at location of next newline
      // (newline is at start of right portion)
      // append newline to left portion
      // and set the selection start and end to the end
      // of the left portion
      const insertionPoint = selectionEnd + nextNewLine // p is offset in v of first newline after selection
      const textLeft = text.substr(0,insertionPoint)
      const textRight = text.substr(insertionPoint)
      const newTextLeft = textLeft + "\n"
      const newText = newTextLeft + textRight
      textarea.value = newText 
      textarea.selectionStart = newTextLeft.length
      textarea.selectionEnd = newTextLeft.length
    }
  }
  setFontSize(size) {
    this.elt.setAttribute("font-size",size)
  }

  // Mobile nav
  moveLeft() {
    const elt = this.elt
    const text = elt.value
    const a = elt.selectionStart
    const b = elt.selectionEnd
    let i = a == 0 ? 0 : a-1
    elt.selectionStart = elt.selectionEnd = i
    elt.focus()
  }
  moveRight() {
    const elt = this.elt
    const text = elt.value
    const a = elt.selectionStart
    const b = elt.selectionEnd
    let i = b < text.length ? b + 1 : text.length
    elt.selectionStart = elt.selectionEnd = i
    elt.focus()
  }
  movePrevHeader() {
    return this.skipPrev("\n#",1)
  }
  moveNextHeader() {
    return this.skipNext("\n#",1)
  }
  skipPrev(what,offset=0,skipPast=false) {
    const elt = this.elt
    const text = elt.value
    const a = elt.selectionStart
    const b = elt.selectionEnd
    this.elt.focus()
    if( a == 0 ) {
      return
    }
    let i = text.substr(0,a-1).lastIndexOf(what)
    if( i >= 0 ) {
      elt.selectionStart = elt.selectionEnd = i + offset
      //console.log(i,text.substr(i,offset),text.substr(i+offset,100))
    } else {
      if( 
        (text.substr(0,what.length) === what ) ||
        skipPast 
      ) {
        elt.selectionStart = elt.selectionEnd = 0
      }
    }
  }
  skipNext(what,offset=0) {
    const elt = this.elt
    const text = elt.value
    const a = elt.selectionStart
    const b = elt.selectionEnd
    const right = text.substr(b+1)
    let i = right.indexOf(what)
    this.elt.focus()
    if( i >= 0 ) {
      elt.selectionStart = elt.selectionEnd = b+i+1+offset
    } 
  }
  prevLine() {
    const elt = this.elt
    const text = elt.value
    const a = elt.selectionStart
    if( a === 0 ) return
    if( text[a-1] !== "\n" ) {
      this.skipPrev("\n",1,true) 
    }
    return this.skipPrev("\n",1,true)
  }
  nextLine() {
    const elt = this.elt
    const text = elt.value
    const a = elt.selectionStart
    if( a >= text.length) return
    if( text[a] === "\n" ) {
      elt.selectionStart = elt.selectionEnd = a+1
      elt.focus()
    } else {
      this.skipNext("\n",1)
    }
  }
}

window.addEventListener("load", _ => {
  const { log } = console
  const q = (x,y=document) => y.querySelector(x)
  const qq = (x,y=document) => Array.from(y.querySelectorAll(x))
  const ajax = new Ajax()
  const ui = new PTUIEdit(ajax)
  window.ptui = ui

  if( window.location.href.match("&version=") ) {
    ui.setDirty();
  }
})
