const KeysUtil = {
  sortCombo: function(x) {
    const ka = x.split("-")
    const k = ka.pop()
    ka.sort()
    ka.push(k)
    return ka.join("-") 
  },
  acms: function(e) {
    // modifiers are applied in lex order: A-C-M-S-key -- when assigning shortcuts, the seq is sorted
    // prior to insertion into the map
    let key = e.key
    const code = e.code.toLowerCase()
    if( code === "backquote" ) { key = "`" }
    if( code.startsWith("digit") ) { key = code.substr(5) }
    if( code === "space" ) { key = "space" }
    let t = key.toLowerCase()
    if( e.shiftKey ) t = "S-"+t
    if( e.metaKey ) t = "M-"+t
    if( e.ctrlKey ) t = "C-"+t
    if( e.altKey ) t = "A-"+t
    return t
  },
  ignored: function(elt) {
    return KeysUtil.ignoredTags.has(elt.tagName.toLowerCase())
  },
  ignoredTags: new Set(["textarea","input"])
}
const UploadUtil = {
  filenameIsImage: function(filename) {
    return filename.match(/\.(jpg|jpeg|jfif|png|webp|gif|svg)$/)
  }
}

class KeyHandler {
  constructor() {
    this.mapf = new Map()
    this.mapn = new Map()
  }
  handle = e => {
    const combo = KeysUtil.acms(e)
    console.log("key1",combo,e)
    if( this.mapf.has(combo) ) {
      this.mapf.get(combo).handle(e)
      return true
    }
    if( KeysUtil.ignored(e.target) ) {
      return false
    }
    if( this.mapn.has(combo) ) {
      this.mapn.get(combo).handle(e)
      return true
    }
    if( this.next ) {
      return this.next(e)
    }
    return false
  }
  help(header) {
    let div = document.createElement("div")
    let h1 = document.createElement("h1")
    if( typeof header === "string" ) {
      h1.textContent = header
    } else {
      h1.append(header)
    }
    div.append(h1)
    let table = document.createElement("table")
    div.append(table)
    let ks = new Set()
    let ds = new Map()
    for( let k of this.mapn.keys() ) {
      ks.add(k) 
      ds.set(k,this.mapn.get(k).desc)
    }
    for( let k of this.mapf.keys() ) {
      ks.add(k) 
      ds.set(k,this.mapf.get(k).desc)
    }
    console.log({ds})
    ks = [...ks]
    ks.sort()
    for( let k of ks ) {
      let tr = document.createElement("tr")
      let td
      td = document.createElement("td")
      td.textContent = k
      tr.append(td)
      td = document.createElement("td")
      td.textContent = ds.get(k)
      tr.append(td)
      table.append(tr)
    }
    return table
  }
  addf(combo,desc,handle) {
    this.mapf.set(combo,{ desc: desc + " (f)", handle })
  }
  addn(combo,desc,handle) {
    this.mapn.set(combo,{ desc, handle })
  }
  addfp(combo,desc,handle) {
    this.mapf.set(combo,{ 
      desc: desc + " (f)", 
      handle: e => {
        e.preventDefault()
        return handle (e)
      }
    })
  }
  addnp(combo,desc,handle) {
    this.mapn.set(combo,{ 
      desc, 
      handle: e => {
        e.preventDefault()
        return handle (e)
      }
    })
  }
}

window.q = (x,y=document) => y.querySelector(x)
window.qq = (x,y=document) => Array.from(y.querySelectorAll(x))

class PTUIBase {
  constructor() {
    window.ptui = this
    this.keys = new KeyHandler()
    this.beforeKeys = null
    window.addEventListener("keydown",e => {
      console.log(this,this.beforeKeys,window.ptui,window.ptui===this)
      if( this.beforeKeys ) {
        if( this.beforeKeys.handle(e)) {
          return
        }
      }
      return this.keys.handle(e)
    })
    this.setupUI()
    this.setupKeys()
  }
  init() {}
  urlWithAction(url,action) {
    let qi = url.indexOf("?")
    if( qi < 0 ) {
      return url + "?action="+action
    }
    let base = url.substr(0,qi)
    let queryString = url.substr(qi+1)
    let m = new Map()
    queryString.split("&").forEach(x => {
      let ei = x.indexOf("=")
      if( ei < 0 ) {
        return m.set(x,"true")
      }
      let k = x.substr(0,ei)
      let v = x.substr(ei+1)
      m.set(k,v)
    })
    m.set("action",action)
    let qs = [...m.entries()].map(([k,v]) => `${k}=${v}`).join("&")
    return `${base}?${qs}`
  }
  hereWithAction(action) {
    return this.urlWithAction(window.location.href,action)
  }
}

class PTUI extends PTUIBase {
  constructor(ajax) {
    super()
    this.ajax = ajax
  }
  high(pattern) {
    let ps = [ 
      ...qq("section.main > p"),
       ...qq("section.main td"),
       ...qq("section.main li") ]
    if(pattern) {
      if( typeof pattern === "string" ) { pattern = new RegExp(pattern,"i") }
      ps.forEach(x => { 
        if( x.textContent.match(pattern) ) { 
          x.classList.add("matching")
        } else { 
          x.classList.remove("matching") } 
      })
    } else {
      ps.forEach(x => x.classList.remove("matching"))
    }
  }
  setupUI() {
    this.infoBox = new InfoBox()
    this.helpBox = new HelpBox()
    this.gotoBox = new GotoBox()
    this.errorBox = new ErrorBox()
    this.previewBox = new PreviewBox()
    this.versionsBox = new VersionsBox()
    this.setupFileDragAndDrop()
  }
  setupFileDragAndDrop() {
    const body = document.body
    body.addEventListener("drop", e => {
      console.log("drop")
      e.preventDefault()
      this.uploadFiles(e)
      body.classList.remove("dragon")
    })
    body.addEventListener("dragover", e => {
      console.log("dragover")
      body.classList.add("dragon")
      e.preventDefault()
    })
    body.addEventListener("dragleave", e => {
      console.log("dragleave")
      body.classList.remove("dragon")
      e.preventDefault()
    })
  }
  uploadFiles(e) {
    this.ajax.upload(e.dataTransfer.files,e => this.uploadFilesResult(e))
  }
  uploadFilesResult(response) {
    // we want to override in PTUIEdit so that we can insert links
    // into the editor. But we want the same message
    let messageContent = this.createUploadFilesResultMessage(response)
    if( response.result === "error" ) {
      this.errorBox.showContent(messageContent)
    } else {
      this.infoBox.showContent(messageContent)
    }
  }
  createUploadFilesResultMessage(response) {
    console.log("uploadFilesResult",response)
    let { error, result, files } = response
    console.log(1235,{files})

    let div = document.createElement("div")
    div.classList.add("upload-result")
    let mdiv,span

    let h1 = document.createElement("h1")
    h1.textContent = "Upload Result"
    div.append(h1)

    let successfulFiles = files.filter(x => x.result === "success" )
    let msg
    if( result === "partial" ) {
      msg = "Partial success" 
    } else if( result === "success") {
      msg = "Success"
    } else if( result === "error") {
      msg = "Error"
    }
    msg += `: ${successfulFiles.length}/${files.length} uploaded.`

    mdiv = document.createElement("div")
    mdiv.classList.add("upload-result-message")
    mdiv.textContent = msg
    div.append(mdiv)

    mdiv = document.createElement("div")
    mdiv.classList.add("upload-result-files")
    div.append(mdiv)

    let fdiv
    for( let file of files ) {
      fdiv = document.createElement("div")
      fdiv.classList.add("upload-result-file")
      let { filename, result, error } = file
      span = document.createElement("span")
      if( result === "error" ) {
        fdiv.classList.add("error")
        span.classList.add("upload-file-error")
        span.textContent = `${filename} &mdash; ${error}`
      } else if( UploadUtil.filenameIsImage(filename)) {
        fdiv.classList.add("success")
        span.classList.add("upload-file-image")
        let fnspan = document.createElement("span")
        fnspan.classList.add("upload-file-filename")
        fnspan.textContent = filename
        span.append(fnspan)
        let img = document.createElement("img")
        img.classList.add("upload-thumbnail")
        img.src = filename
        span.append(img)
      } else {
        fdiv.classList.add("success")
        span.classList.add("upload-file-normal")
        let fnspan = document.createElement("span")
        fnspan.classList.add("upload-file-filename")
        fnspan.textContent = filename
        span.append(fnspan)
      }
      fdiv.append(span)
      mdiv.append(fdiv)
    }
    return div
  }
  getUriInfo() {
    let uri = window.location.href
    let m = uri.match(new RegExp("^https?:\//[^/]+/((.*/)?([^/?]*))(?:\\?(.*))?$"))
    let [ all, local, subdir, pagename, qs ] = m
    let d = { all, local, subdir, pagename, qs }
    return d
  }
  replaceOverlay(elt) {
    this.hideOverlay()
    this.overlay = elt
  }
  hideOverlay() {
    if( this.overlay ) this.overlay.hide()
  }
  setupKeys() {
    const f = (t,d,h) => this.keys.addfp(t,d,h)
    const n = (t,d,h) => this.keys.addnp(t,d,h)
    n("S-g","show gotobox",      e => this.gotoBox.show())
    f("C-h","show help",         e => this.helpBox.showHelp())
    f("C-g","show gotobox(f)",   e => this.gotoBox.show())
    f("escape","hide overlays",  e => {
      this.hideOverlay()
    })
    f("C-i","hello world",       e => {
      this.infoBox.showContent("hello world")
    })
    f("C-S-i","hello world html", e => {
      let h1 = document.createElement("h1")
      h1.textContent = "hello world h1"
      this.infoBox.showContent(h1)
    })
    f("C-S-d","duplicate tab in view mode", e => this.duplicateView())
    f("C-S-f","duplicate tab in edit mode", e => this.duplicateEdit())
    f("C-1","toggle wide mode", e => document.body.classList.toggle("full-width"))
    f("C-2","toggle hide header", e => document.body.classList.toggle("hide-header"))
  }

  /////////////
  // Methods common to all PTUIs 
  duplicateView() {
    let url = this.hereWithAction("view")
    window.open(url,'_blank')
  }
  duplicateEdit() {
    let url = this.hereWithAction("edit")
    window.open(url,'_blank')
  }
}
    
class Overlay {
  // requires this.elt to be a DOM element
  // only one shown at a time
  constructor(title="Overlay") {
    this.elt = document.createElement("div")
    this.elt.classList.add("overlay")
    this.createElements()
    this.setTitle(title)
    document.body.append(this.elt)
  }
  createElements() {
    this.header = document.createElement("header")
    this.elt.append(this.header)
    this.topBar = document.createElement("section")
    this.topBar.classList.add("topbar")
    this.overlayTitle = document.createElement("span")
    this.overlayTitle.classList.add("overlay-title")
    this.overlayTitle.classList.add("spacer")
    this.topBar.append(this.overlayTitle)
    this.closeButton = document.createElement("span")
    this.closeButton.classList.add("close-button")
    this.closeButton.classList.add("block")
    this.closeButton.classList.add("action")
    this.closeButton.innerHTML = "&#x274C;"
    this.closeButton.addEventListener("click",e => this.hide())
    this.topBar.append(this.closeButton)
    this.header.append(this.topBar)
    this.elt.append(this.header)
    this.contentDiv = document.createElement("div")
    this.contentDiv.classList.add("overlay-content")
    this.elt.append(this.contentDiv)
  }
  setTitle(title) {
    this.title = title
    this.overlayTitle.textContent = title
  }
  show(timeout=2000) {
    if( this.timeout ) clearTimeout(this.timeout)
    window.ptui.replaceOverlay(this)  
    this.elt.classList.add("visible")
    if( timeout > 0 ) 
    {
      this.timeout = setTimeout(_ => this.hide(),timeout)
    }
  }
  hide() {
    this.elt.classList.remove("visible")
    if( window.ptui.overlay === undefined ) return
    if( window.ptui.overlay === this ) {
      window.ptui.overlay = undefined
    } else {
      console.warn("Current overlay is not this one")
    }
  }
  showHtml(html,timeout=2000) {
    let content = document.createElement("div")
    content.innerHTML = html
    return this.showContent(content,timeout)
  }
  showContent(content,timeout=2000) {
    if( typeof content === "string" ) {
      this.contentDiv.textContent = content
    } else {
      this.contentDiv.textContent = ""
      this.contentDiv.append(content)
    }
    this.show(timeout)
    this.focus()
  }
  focus() {
    this.contentDiv.focus()
  }
}

class InfoBox extends Overlay {
  constructor() {
    super("Info")
    this.elt.classList.add("info-box")
  }
}

class ErrorBox extends Overlay {
  constructor() {
    super("Error")
    this.elt.classList.add("error-box")
  }
}

class HelpBox extends Overlay {
  constructor() {
    super("Help")
    this.elt.classList.add("help-box")
  }
  showHelp() {
    return this.showKeyboardShortcuts()
  }
  showKeyboardShortcuts(timeout=20000) {
    let ptui = window.ptui
    let elt = document.createElement("div")
    elt.classList.add("keyboard-shortcuts")
    if( ptui.editor ) {
      let keys = window.ptui.editor.keys
      let help = keys.help("Editor Keys")
      elt.append(help)
    }
    let keys = window.ptui.keys
    let help = keys.help("Global Keys")
    elt.append(help)
    this.showContent(help,timeout)
  }
}

class Editor {
  constructor(elt) {
    this.elt = elt
    this.keys = new KeyHandler()
    this.elt.addEventListener("keydown",this.keys.handle)
    this.setupKeys()
    this.elt.style.border = "3px solid green"
  }
  setupKeys() {
    const f = (t,d,h) => this.keys.addfp(t,d,h)
    f("tab","insert tab", e => {
      this.elt.value += "tab"
    })
  }
}

class UIDialog {
  constructor() {
    this.actualDialog = document.createElement("dialog")
    this.actualDialog.classList.add("actual-dialog")
    this.elt = document.createElement("div")
    this.elt.classList.add("dialog-div")
    this.actualDialog.append(this.elt)
    this.keys = new KeyHandler()
    this.modal = true
    this.createElements()
    this.setupKeys()
    this.setupClick()
    document.body.append(this.actualDialog)
  }
  addClass(cls) {
    this.actualDialog.classList.add(cls)
    this.elt.classList.add(cls)
  }
  setupClick() {
    this.actualDialog.addEventListener("click",e => {
      if( e.target === this.actualDialog ) {
        e.preventDefault()
        this.hide()
      }
    })
  }
  setKeyListener(elt) {
    elt.addEventListener("keydown",this.handleKey)
  }
  createElements() { }
  setupKeys() { }
  show() {
    window.ptui.replaceOverlay(this)
    window.ptui.beforeKeys = this.keys
    if( this.modal ) {
      this.actualDialog.showModal()
    } else {
      this.actualDialog.show()
    }
    this.didShow()
  }
  didShow() {}
  hide() {
    if( window.ptui.beforeKeys === this.keys ) {
      window.ptui.beforeKeys = null
    }
    this.actualDialog.close()
  }
}

class UIDialogWithTitle extends UIDialog {
  constructor(title) {
    super()
    this.title = title
    this.elt.classList.add("ui-dialog-with-title")
  }
  createElements() {
    this.titleBar = document.createElement("div")
    this.titleBar.classList.add("title-bar")
    this.titleBarText = document.createElement("span")
    this.titleBarText.classList.add("title-bar-text")
    this.titleBarText.textContent = this.title
    this.titleBar.append(titleBarText)
    this.titleBar.textContent = this.title
    this.closeButton = document.createElement("span")
    this.closeButton.classList.add("close-button")
    this.closeButton.classList.add("block")
    this.closeButton.classList.add("action")
    this.closeButton.innerHTML = "&#x274C;"
    this.closeButton.addEventListener("click",e => this.hide())
    this.titleBar.append(this.closeButton)
    this.elt.insertBefore(titleBar,this.elt.firstChild)
  }
}

class PreviewBox extends UIDialog {
  createElements() {
    this.addClass("preview-box")
    this.addClass("left-justify")
    this.header = document.createElement("header")
    this.elt.append(this.header)
    this.topBar = document.createElement("section")
    this.topBar.classList.add("topbar")
    this.spacer = document.createElement("span")
    this.spacer.classList.add("spacer")
    this.spacer.classList.add("block")
    this.topBar.append(this.spacer)
    this.pageName = document.createElement("span")
    this.pageName.classList.add("pagename-container")
    this.pageName.classList.add("block")
    this.topBar.append(this.pageName)
    this.pageNameBefore = document.createElement("span")
    this.pageNameBefore.classList.add("pagename-before")
    this.pageNameBefore.textContent = "Preview:"
    this.pageNameName = document.createElement("span")
    this.pageNameName.classList.add("pagename-name")
    this.pageName.append(this.pageNameBefore)
    this.pageName.append(this.pageNameName)
    this.spacer2 = document.createElement("span")
    this.spacer2.classList.add("spacer2")
    this.spacer2.classList.add("block")
    this.topBar.append(this.spacer2)
    this.closeButton = document.createElement("span")
    this.closeButton.classList.add("close-button")
    this.closeButton.classList.add("block")
    this.closeButton.classList.add("action")
    this.closeButton.innerHTML = "&#x274C;"
    this.closeButton.addEventListener("click",e => this.hide())
    this.topBar.append(this.closeButton)
    this.header.append(this.topBar)
    this.infoBar = document.createElement("section")
    this.infoBar.classList.add("info-bar")
    this.header.append(this.infoBar)
    this.info = document.createElement("div")
    this.info.classList.add("info")
    this.info.classList.add("spreadwide")
    this.pagePath = document.createElement("span")
    this.pagePath.classList.add("page-path")
    this.info.append(this.pagePath)
    this.infoBar.append(this.info)
    this.content = document.createElement("section")
    this.content.classList.add("main")
    this.elt.append(this.content)
  }
  showPreview(pageName,pagePath,html) {
    console.log("preview",html)
    this.pageNameName.textContent = pageName
    this.pagePath.textContent = pagePath
    this.content.textContent = ""
    this.content.append(html)
    this.show()
  }
  didShow() {
    this.content.focus()
  }
}

class GotoBox extends UIDialog {
  constructor() {
    super()
    this.openInEdit = false
    this.openInNewTab = false
  }
  createElements() {
    this.actualDialog.classList.add("goto-box")
    this.header = document.createElement("header")
    this.elt.append(this.header)
    this.topBar = document.createElement("section")
    this.topBar.classList.add("topbar")
    this.spacer = document.createElement("span")
    this.spacer.classList.add("spacer")
    this.spacer.classList.add("block")
    this.topBar.append(this.spacer)
    this.title = document.createElement("span")
    this.title.classList.add("title")
    this.title.textContent = "Goto"
    this.topBar.append(this.title)
    this.spacer = document.createElement("span")
    this.spacer.classList.add("spacer")
    this.spacer.classList.add("block")
    this.topBar.append(this.spacer)
    this.closeButton = document.createElement("span")
    this.closeButton.classList.add("close-button")
    this.closeButton.classList.add("block")
    this.closeButton.classList.add("action")
    this.closeButton.innerHTML = "&#x274C;"
    this.closeButton.addEventListener("click",e => this.hide())
    this.topBar.append(this.closeButton)
    this.header.append(this.topBar)
    this.content = document.createElement("section")
    this.content.classList.add("content")
    this.elt.append(this.content)
    this.input = document.createElement("input")
    this.setKeyListener(this.input)
    this.input.setAttribute("placeholder","page or url")
    this.flags = document.createElement("span")
    this.flags.classList.add("goto-flags")
    this.newTabFlag = document.createElement("span")
    this.newTabFlag.innerHTML = "New&nbsp;Tab"
    this.newTabFlag.classList.add("open-in-new-tab")
    this.newTabFlag.classList.add("flag")
    this.newTabFlag.setAttribute("state","off")
    this.newTabFlag.addEventListener("click", e => {
      e.preventDefault()
      this.toggleNewTab()
    })
    this.editFlag = document.createElement("span")
    this.editFlag.textContent = "Edit"
    this.editFlag.classList.add("open-in-edit-mode")
    this.editFlag.classList.add("flag")
    this.editFlag.setAttribute("state","off")
    this.editFlag.addEventListener("click", e => {
      e.preventDefault()
      this.toggleEditMode()
    })
    this.flags.append(this.newTabFlag)
    this.flags.append(this.editFlag)
    this.content.append(this.input)
    this.content.append(this.flags)
  }
  toggleNewTab() {
    this.openInNewTab = !this.openInNewTab
    this.newTabFlag.setAttribute("state",this.openInNewTab ? "on" : "off")
  }
  toggleEditMode() {
    this.openInEditMode = !this.openInEditMode
    this.editFlag.setAttribute("state",this.openInEditMode ? "on" : "off")
  }
  didShow() {
    this.input.focus()
  }
  exec() {
    let dest = this.input.value
    dest = dest.split("?")[0]
    if( this.openInEditMode ) {
      dest += `?action=edit`
    }
    console.log({goto:dest,newtab:this.openInNewTab,edit:this.openInEditMode})
    if( this.openInNewTab ) {
      window.open(dest,"_blank")
    } else {
      window.location.href = dest
    }
  }
  setupKeys() {
    const f = (t,d,h) => this.keys.addfp(t,d,h)
    const n = (t,d,h) => this.keys.addnp(t,d,h)
    f("enter","goto url",e => this.exec())
    f("C-d","toggle new tab",e => this.toggleNewTab())
    f("C-e","toggle edit mode",e => this.toggleEditMode())
  }
}

class VersionsBox extends UIDialog {
  createVersionItem(pagename,version) {
    let row = document.createElement("tr")
    let datetime
    if( version ) {
      function pad(x,n=2,c="0") {
        return `${x}`.padStart(n,c)
      }
      datetime = new Date(1000*version)
      let year = datetime.getFullYear()
      let month = pad(datetime.getMonth())
      let day = pad(datetime.getDay())
      let hours = pad(datetime.getHours())
      let minutes = pad(datetime.getMinutes())
      let seconds = pad(datetime.getSeconds())
      datetime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
    } else {
      datetime = "current"
    }
    let td = document.createElement("td")
    row.append(td)
    td.classList.add("version-datetime")
    td.textContent = `${datetime}`
    td = document.createElement("td")
    row.append(td)
    td.classList.add("version-controls")
    let hrefedit = `${pagename}?action=edit`
    let hrefview = `${pagename}?action=view`
    if( version ) {
      hrefedit += `&version=${version}`
      hrefview += `&version=${version}`
    }
    let a = document.createElement("a")
    a.textContent = "View"
    a.setAttribute("href",hrefview)
    a.classList.add("view-link")
    td.append(a)
    td.append(document.createTextNode(" "))
    a = document.createElement("a")
    a.textContent = "Edit"
    a.setAttribute("href",hrefedit)
    a.classList.add("edit-link")
    td.append(a)
    return row
  }
  makeContent(pagename,path,versions) {
    versions.sort()
    versions.reverse()
    this.pageNameName.textContent = `${path}`
    this.table.innerHTML = ""
    this.table.append(this.createVersionItem(pagename))
    versions.forEach(version => {
      let row = this.createVersionItem(pagename,version)
      this.table.append(row)
    })
  }
  showVersions(pagename,path,versions) {
    this.makeContent(pagename,path,versions)
    this.show()
  }
  createElements() {
    this.addClass("versions-box")
    this.addClass("left-justify")
    this.header = document.createElement("header")
    this.elt.append(this.header)
    this.topBar = document.createElement("section")
    this.topBar.classList.add("topbar")
    this.spacer = document.createElement("span")
    this.spacer.classList.add("spacer")
    this.spacer.classList.add("block")
    this.topBar.append(this.spacer)
    this.pageName = document.createElement("span")
    this.pageName.classList.add("pagename-container")
    this.pageName.classList.add("block")
    this.topBar.append(this.pageName)
    this.pageNameBefore = document.createElement("span")
    this.pageNameBefore.classList.add("pagename-before")
    this.pageNameBefore.textContent = "Versions of: "
    this.pageNameName = document.createElement("span")
    this.pageNameName.classList.add("pagename-name")
    this.pageName.append(this.pageNameBefore)
    this.pageName.append(this.pageNameName)
    this.spacer2 = document.createElement("span")
    this.spacer2.classList.add("spacer2")
    this.spacer2.classList.add("block")
    this.topBar.append(this.spacer2)
    this.closeButton = document.createElement("span")
    this.closeButton.classList.add("close-button")
    this.closeButton.classList.add("block")
    this.closeButton.classList.add("action")
    this.closeButton.innerHTML = "&#x274C;"
    this.closeButton.addEventListener("click",e => this.hide())
    this.topBar.append(this.closeButton)
    this.header.append(this.topBar)
    this.content = document.createElement("section")
    this.content.classList.add("main")
    this.elt.append(this.content)
    this.table = document.createElement("table")
    this.table.classList.add("version-list")
    this.content.append(this.table)
    this.elt.append(this.content)
  }
}

function test() {
  let ptui = new PTUI()
}
