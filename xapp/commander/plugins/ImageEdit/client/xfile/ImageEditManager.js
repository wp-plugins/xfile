define([
    'dojo/_base/declare',
    'xide/utils',
    'xide/factory',
    'xide/types',
    'xapp/manager/ManagerBase',
    'xfile/views/RemoteEditor'

], function (declare,
             utils,
             factory,
             types,
             ManagerBase,RemoteEditor)
{
    return declare("ImageEdit.xfile.ImageEditManager", [ManagerBase],
        {
            mainView:null,
            ctx:null,
            config:null,
            panelManager:null,
            fileManager:null,
            imageEditView:null,
            currentItem:null,
            imageEditorPane:null,
            imageEditorPaneContainer:null,
            featherEditor:null,
            onEditorClose:function(editor){
                this.featherEditor=editor;
                //this.imageEditorPaneContainer.removeChild(this.imageEditorPane);
                this.ctx.getPanelManager().onClosePanel(editor);
            },
            onImageSaved:function(newUrl){
                var fileManager = this.fileManager || xfile.getContext().getFileManager();
                if(fileManager){
                    fileManager.downloadTo(newUrl,this.currentItem.mount,this.currentItem.path);
                }
            },
            getFileName:function(){
                var imageUrl = this.config.REPO_URL + '/' +this.currentItem.path;
                imageUrl=imageUrl.replace('./','/');
                return imageUrl;
            },
            getMainView:function(){
              return this.mainView || this.panelManager.rootView;
            },
            onItemSelected:function(eventData){
                this.currentItem = eventData.item;
            },
            /**
             * Open Aviary instance
             * @param item
             */
            openEditor:function(item){

                var mainView = this.getMainView();
                var thiz=this;
                if(!mainView){
                    return;
                }
                var dstContainer = mainView.getNewAlternateTarget();
                if(!dstContainer){
                    return;
                }
                this.imageEditorPaneContainer=dstContainer;

                var fileManager = this.ctx.getFileManager();
                var imageUrl = fileManager.getImageUrl(item);
                if(imageUrl.indexOf('http')==-1){
                    imageUrl = this.config.REPO_URL + '/' + fileManager.getImageUrl(item);
                }
                var _container = new dijit.layout.ContentPane({
                    title:item.name,
                    closable:true,
                    style:'padding:0px;margin:0px;overflow:hidden;',
                    onClose: function () {
                        thiz.onEditorClose(this);
                    }
                },dojo.doc.createElement('div'));

                dstContainer.addChild(_container);
                dstContainer.selectChild(_container);
                dstContainer.resize();
                mainView.resize();
                dstContainer=_container;

                this.imageEditorPane=_container;

                if (this.imageEditView){
                    utils.destroyWidget(this.imageEditView);
                    this.imageEditView=null;
                }
                if (!this.imageEditView) {
                    console.log('open image editor with image url ' + imageUrl);
                    this.imageEditView = new RemoteEditor({
                        selected:true,
                        delegate:this,
                        options:{},
                        config:this.config,
                        frameUrl:require.toUrl("ImageEdit/xfile/templates/Aviary.html"),
                        editUrl:imageUrl,
                        parentContainer:dstContainer
                    },dojo.doc.createElement('div'));

                    dstContainer.containerNode.appendChild(this.imageEditView.domNode);
                }
                this.imageEditView.startup();
            },
            openPixlrEditor:function(item){

                var mainView = this.getMainView();
                var thiz=this;
                if(!mainView){
                    return;
                }
                var dstContainer = mainView.getNewAlternateTarget();
                if(!dstContainer){
                    return;
                }
                this.imageEditorPaneContainer=dstContainer;

                var fileManager = this.ctx.getFileManager();
                var imageUrl = fileManager.getImageUrl(item);
                if(imageUrl.indexOf('http')==-1){
                    imageUrl = this.config.REPO_URL + '/' + fileManager.getImageUrl(item);
                }

                var _container = new dijit.layout.ContentPane({
                    title:item.name,
                    closable:true,
                    style:'padding:0px;margin:0px;overflow:hidden;',
                    onClose: function () {
                        thiz.onEditorClose(this);
                    }
                },dojo.doc.createElement('div'));

                dstContainer.addChild(_container);
                dstContainer.selectChild(_container);
                dstContainer.resize();
                mainView.resize();

                dstContainer=_container;
                this.imageEditorPane=_container;

                if (this.imageEditView){
                    utils.destroyWidget(this.imageEditView);
                    this.imageEditView=null;
                }
                //console.log('save url ' + this.config.FILE_SERVICE_FULL;
                //'?service=XCOM_Directory_Service.fileUpdate&callback=asdf&mount='+item.mount +'&srcPath='+item.path);
                var saveUrl = this.config.FILE_SERVICE_FULL;
                var mount=item.mount.replace('/','');
                if(saveUrl.indexOf('?')==-1){
                    saveUrl+='?service=XCOM_Directory_Service.fileUpdate&view=smdCall&callback=asdf&mount='+mount +'&srcPath='+item.path;
                }else{
                    saveUrl+='&service=XCOM_Directory_Service.fileUpdate&view=smdCall&callback=asdf&mount='+mount +'&srcPath='+item.path;
                }
                console.log('save url ' + saveUrl);
                if (!this.imageEditView) {
                    this.imageEditView = new RemoteEditor({
                        selected:true,
                        delegate:this,
                        options:{},
                        config:this.config,
                        frameUrl:require.toUrl("ImageEdit/xfile/templates/Pixlr.html"),
                        editUrl:imageUrl,
                        saveUrl:saveUrl,
                        parentContainer:dstContainer,
                        title:item.name
                    },dojo.doc.createElement('div'));

                    dstContainer.containerNode.appendChild(this.imageEditView.domNode);
                }
                this.imageEditView.startup();

            },
            onMainViewReady:function(){
                var thiz=this;
                factory.publish(types.EVENTS.REGISTER_EDITOR,{
                    name:'Aviary',
                    extensions:'jpeg|jpg|gif|png',
                    onEdit:function(){thiz.openEditor(thiz.currentItem)},
                    iconClass:'el-icon-brush',
                    owner:thiz
                },thiz);

                factory.publish(types.EVENTS.REGISTER_EDITOR,{
                    name:'Pixlr',
                    extensions:'jpeg|jpg|gif|png',
                    onEdit:function(){thiz.openPixlrEditor(thiz.currentItem)},
                    iconClass:'el-icon-brush',
                    owner:thiz
                },thiz);
            },
            _registerListeners:function () {
                this.inherited(arguments);
                factory.subscribe(types.EVENTS.ITEM_SELECTED,this.onItemSelected,this);
                factory.subscribe(types.EVENTS.ON_MAIN_VIEW_READY,this.onMainViewReady,this);
            },
            constructor:function () {
                this._registerListeners();
            }
        });
});