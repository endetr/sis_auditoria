<?php
/**
 *@package pXP
 *@file OportunidadMejoraInforme.php
 *@author  Maximilimiano Camacho
 *@date 24-07-2019
 *@description Archivo con la interfaz de usuario que permite
 *planificar Auditoria.
 *
 */
header("content-type: text/javascript; charset=UTF-8");
?>
<script>
    Phx.vista.OportunidadMejoraInforme = {
        bedit:false,
        bnew:false,
        bsave:false,
        bdel:false,
        bodyStyleForm: 'padding:5px;',
        borderForm: true,
        frameForm: false,
        paddingForm: '5 5 5 5',
        require:'../../../sis_auditoria/vista/auditoria_oportunidad_mejora/AuditoriaOportunidadMejora.php',
        requireclase:'Phx.vista.AuditoriaOportunidadMejora',
        title:'AuditoriaOportunidadMejora',
        nombreVista: 'OportunidadMejoraInforme',
        storePunto: {},
        tienda:{},
        constructor: function(config) {
         
            this.idContenedor = config.idContenedor;
            Phx.vista.OportunidadMejoraInforme.superclass.constructor.call(this,config);
            this.recomendacionForm();
            this.store.baseParams.interfaz = this.nombreVista;
            this.init();
            this.load({params:{start:0, limit:this.tam_pag}});
        },
        EnableSelect: function(){
            Phx.vista.OportunidadMejoraInforme.superclass.EnableSelect.call(this);
        },
        onButtonEdit:function(){
            this.onCrearFormulario();
            this.abrirVentana('edit');
        },
        abrirVentana: function(tipo){
            if(tipo === 'edit'){
                this.cargaFormulario(this.sm.getSelected().data);
                // this.onEdit(this.sm.getSelected().data);
            }
            this.formularioVentana.show();
        },
        onCrearFormulario:function() {
            if(this.formularioVentana) {
                this.form.destroy();
                this.formularioVentana.destroy();
            }
            const me = this;
            const maestro = this.sm.getSelected().data;
            this.tienda = new Ext.data.JsonStore({
                url: '../../sis_auditoria/control/NoConformidad/listarNoConformidad',
                id: 'id_nc',
                root: 'datos',
                totalProperty: 'total',
                fields: ['id_aom','id_nc','valor_parametro','estado_wf','descrip_nc',
                    'calidad',
                    'medio_ambiente',
                    'seguridad',
                    'responsabilidad_social',
                    'sistemas_integrados',
                    'obs_resp_area',
                    'obs_consultor',
                    'evidencia',
                    'id_parametro',
                    'id_pnnc'
                ],
                remoteSort: true,
                baseParams: {dir:'ASC',sort:'id_nc',limit:'100',start:'0'}
            });
            this.tienda.baseParams.id_aom = maestro.id_aom;
            this.tienda.load();
            const noConformidad = new Ext.grid.GridPanel({
                layout: 'fit',
                store: this.tienda,
                region: 'center',
                trackMouseOver: false,
                split: true,
                border: true,
                plain: true,
                stripeRows: true,
                tbar: [{
                    text: '<i class="fa fa-plus fa-lg">&nbsp;&nbsp;Asignar</i>',
                    scope: this,
                    width: '100',
                    handler: function() {
                        me.formularioNoConformidad(null);
                        me.ventanaNoConformidad.show();
                    },
                },
                    {
                        text: '<i class="fa fa-edit fa-lg">&nbsp;&nbsp;Editar</i>',
                        scope:this,
                        width: '100',
                        handler: function(){
                            const  s =  noConformidad.getSelectionModel().getSelections();
                            me.formularioNoConformidad(s[0].data);
                            me.ventanaNoConformidad.show();
                        }
                    },
                    {
                        text: '<i class="fa fa-trash fa-lg">&nbsp;&nbsp;Eliminar</i>',
                        scope:this,
                        width: '100',
                        handler: function(){
                            const  s =  noConformidad.getSelectionModel().getSelections();
                            Phx.CP.loadingShow();
                            Ext.Ajax.request({
                                url: '../../sis_auditoria/control/NoConformidad/eliminarNoConformidad',
                                params: {
                                    id_nc : s[0].data.id_nc
                                },
                                isUpload: false,
                                success: function(a,b,c){
                                    Phx.CP.loadingHide();
                                    me.tienda.load();
                                },
                                argument: this.argumentSave,
                                failure: this.conexionFailure,
                                timeout: this.timeout,
                                scope: this
                            })
                        }
                    }
                ],
                columns: [
                    new Ext.grid.RowNumberer(),
                    {
                        header: 'Tipo',
                        dataIndex: 'valor_parametro',
                        align: 'center',
                        width: 100,
                        renderer : function(value, p, record) {
                            return String.format('<div class="gridmultiline" style=" font-size: 10px; ">{0}</div>', record.data['valor_parametro']);
                        }
                    },
                    {
                        header: 'Descripcion',
                        dataIndex: 'descrip_nc',
                        align: 'justify',
                        width: 400,
                        renderer : function(value, p, record) {
                            return String.format('<div class="gridmultiline" style=" font-size: 10px; ">{0}</div>', record.data['descrip_nc']);
                        }
                    },
                    {
                        header: 'Estado',
                        dataIndex: 'estado_wf',
                        align: 'center',
                        width: 150,
                        renderer : function(value, p, record) {
                            return String.format('<div class="gridmultiline" style=" font-size: 10px; ">{0}</div>', record.data['estado_wf']);
                        }
                    }
                ]
            });
            this.form = new Ext.form.FormPanel({
                id: this.idContenedor + '_formulario_aud',
                items: [{ region: 'center',
                    layout: 'column',
                    border: false,
                    autoScroll: true,
                    items: [{
                        xtype: 'tabpanel',
                        plain: true,
                        activeTab: 0,
                        height: 600,
                        deferredRender: false,
                        items: [{
                            title: 'Datos Principales',
                            layout: 'form',
                            defaults: {
                                width: 600,
                            },
                            autoScroll: true,
                            defaultType: 'textfield',
                            items: [
                                new Ext.form.FieldSet({
                                    collapsible: false,
                                    border : false,
                                    items: [

                                        {
                                            xtype: 'combo',
                                            name: 'id_gconsultivo',
                                            fieldLabel: 'Grupo Consultivo',
                                            allowBlank: false,
                                            id: this.idContenedor+'_id_gconsultivo',
                                            emptyText: 'Elija una opción...',
                                            store: new Ext.data.JsonStore({
                                                url: '../../sis_auditoria/control/GrupoConsultivo/listarGrupoConsultivo',
                                                id: 'id_gconsultivo',
                                                root: 'datos',
                                                sortInfo: {
                                                    field: 'nombre_gconsultivo',
                                                    direction: 'ASC'
                                                },
                                                totalProperty: 'total',
                                                fields: ['id_gconsultivo', 'nombre_gconsultivo','requiere_programacion','requiere_formulario','nombre_programacion','nombre_formulario'],//208
                                                remoteSort: true,
                                                baseParams: {par_filtro: 'gct.nombre_gconsultivo'}
                                            }),
                                            valueField: 'id_gconsultivo',
                                            displayField: 'nombre_gconsultivo',
                                            gdisplayField: 'nombre_gconsultivo',
                                            hiddenName: 'id_gconsultivo',
                                            mode: 'remote',
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            pageSize: 15,
                                            minChars: 2,
                                            anchor: '100%'
                                        },

                                        {
                                            xtype: 'combo',
                                            name: 'id_tipo_om',
                                            fieldLabel: 'Tipo OM',
                                            allowBlank: false,
                                            emptyText: 'Elija una opción...',
                                            id: this.idContenedor+'_id_tipo_om',
                                            store: new Ext.data.JsonStore({
                                                url: '../../sis_auditoria/control/Parametro/listarParametro',
                                                id: 'id_parametro',
                                                root: 'datos',
                                                sortInfo: {
                                                    field: 'valor_parametro',
                                                    direction: 'DESC'
                                                },
                                                totalProperty: 'total',
                                                fields: ['id_parametro', 'tipo_parametro', 'valor_parametro'],
                                                remoteSort: true,
                                                baseParams: {par_filtro: 'prm.id_tipo_parametro',tipo_parametro:'TIPO_OPORTUNIDAD_MEJORA'}
                                            }),
                                            valueField: 'id_parametro',
                                            displayField: 'valor_parametro',
                                            gdisplayField: 'desc_tipo_om',
                                            hiddenName: 'id_tipo_om',
                                            mode: 'remote',
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            pageSize: 15,
                                            minChars: 2,
                                            anchor: '100%'
                                        },
                                        {
                                            xtype: 'field',
                                            name: 'nro_tramite_wf',
                                            fieldLabel: 'Codigo',
                                            anchor: '100%',
                                            readOnly :true,
                                            id: this.idContenedor+'_nro_tramite_wf',
                                            style: 'background-image: none;'
                                        },
                                        {
                                            xtype: 'field',
                                            name: 'estado_wf',
                                            fieldLabel: 'Estado',
                                            anchor: '100%',
                                            readOnly :true,
                                            id: this.idContenedor+'_estado_wf',
                                            style: 'background-image: none;'

                                        },
                                        {
                                            xtype: 'field',
                                            fieldLabel: 'Area',
                                            name: 'nombre_unidad',
                                            anchor: '100%',
                                            readOnly :true,
                                            id: this.idContenedor+'_nombre_unidad',
                                            style: 'background-image: none;'

                                        },
                                        {
                                            xtype: 'field',
                                            fieldLabel: 'Nombre',
                                            name: 'nombre_aom1',
                                            anchor: '100%',
                                            readOnly :true,
                                            id: this.idContenedor+'_nombre_aom1',
                                            style: 'background-image: none;'

                                        },
                                        {
                                            xtype: 'datefield',
                                            fieldLabel: 'Inicio Real',
                                            name: 'fecha_prog_inicio',
                                            disabled: false,
                                            id: this.idContenedor+'_fecha_prog_inicio',
                                            readOnly :true,
                                            anchor: '100%',
                                            style: 'background-image: none;'
                                        },
                                        {
                                            xtype: 'datefield',
                                            fieldLabel: 'Fin Real',
                                            name: 'fecha_prog_fin',
                                            disabled: false,
                                            id: this.idContenedor+'_fecha_prog_fin',
                                            readOnly :true,
                                            anchor: '100%',
                                            style: 'background-image: none;'
                                        },
                                        {
                                            xtype: 'combo',
                                            name: 'id_funcionario',
                                            fieldLabel: 'Auditor Reponsable',
                                            allowBlank: false,
                                            emptyText: 'Elija una opción...',
                                            id: this.idContenedor+'_id_funcionario',
                                            emptyText: 'Elija una opción...',
                                            store: new Ext.data.JsonStore({
                                                url: '../../sis_auditoria/control/AuditoriaOportunidadMejora/getListFuncionario',
                                                id: 'id_funcionario',
                                                root: 'datos',
                                                sortInfo: {
                                                    field: 'desc_funcionario1',
                                                    direction: 'ASC'
                                                },
                                                totalProperty: 'total',
                                                fields: ['id_funcionario','desc_funcionario1','descripcion_cargo','cargo_equipo'],
                                                remoteSort: true,
                                                baseParams: {par_filtro: 'fu.desc_funcionario1'}
                                            }),
                                            valueField: 'id_funcionario',
                                            displayField: 'desc_funcionario1',
                                            gdisplayField: 'desc_funcionario2',
                                            hiddenName: 'id_funcionario',
                                            mode: 'remote',
                                            anchor: '100%',
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            pageSize: 15,
                                            minChars: 2,
                                            readOnly :true,
                                            style: 'background-image: none;'
                                        },
                                        {
                                            xtype: 'combo',
                                            name: 'id_destinatario',
                                            fieldLabel: 'Destinatario',
                                            allowBlank: false,
                                            emptyText: 'Elija una opción...',
                                            id: this.idContenedor+'_id_destinatario',
                                            emptyText: 'Elija una opción...',
                                            store: new Ext.data.JsonStore({
                                                url: '../../sis_auditoria/control/AuditoriaOportunidadMejora/getListFuncionario',
                                                id: 'id_funcionario',
                                                root: 'datos',
                                                sortInfo: {
                                                    field: 'desc_funcionario1',
                                                    direction: 'ASC'
                                                },
                                                totalProperty: 'total',
                                                fields: ['id_funcionario','desc_funcionario1','descripcion_cargo','cargo_equipo'],
                                                remoteSort: true,
                                                baseParams: {par_filtro: 'fu.desc_funcionario1', codigo:'RESP'}
                                            }),
                                            valueField: 'id_funcionario',
                                            displayField: 'desc_funcionario1',
                                            gdisplayField: 'desc_funcionario_destinatario',
                                            hiddenName: 'id_destinatario',
                                            mode: 'remote',
                                            anchor: '100%',
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            pageSize: 15,
                                            minChars: 2,
                                        },
                                        {
                                            anchor: '100%',
                                            bodyStyle: 'padding:10px;',
                                            title: 'Destinatarios Adicionales',
                                            items:[{
                                                xtype: 'itemselector',
                                                name: 'id_destinatarios',
                                                fieldLabel: 'Destinatarios',
                                                imagePath: '../../../pxp/lib/ux/images/',
                                                drawUpIcon:false,
                                                drawDownIcon:false,
                                                drawTopIcon:false,
                                                drawBotIcon:false,
                                                multiselects: [{
                                                    width: 250,
                                                    height: 200,
                                                    store: new Ext.data.JsonStore({
                                                        url: '../../sis_auditoria/control/AuditoriaOportunidadMejora/getListAuditores',
                                                        id: 'id_funcionario',
                                                        root: 'datos',
                                                        sortInfo: {
                                                            field: 'desc_funcionario1',
                                                            direction: 'DESC'
                                                        },
                                                        totalProperty: 'total',
                                                        fields: ['id_funcionario', 'desc_funcionario1'],
                                                        remoteSort: true,
                                                        autoLoad: true,
                                                        baseParams: {
                                                            dir:'ASC',
                                                            sort:'id_aom',
                                                            limit:'100',
                                                            start:'0',
                                                            codigo:'MEQ',
                                                            destinatario: maestro.id_aom
                                                        }
                                                    }),
                                                    displayField: 'desc_funcionario1',
                                                    valueField: 'id_funcionario',
                                                },
                                                    {
                                                        width: 250,
                                                        height: 200,
                                                        store: new Ext.data.JsonStore({
                                                            url: '../../sis_auditoria/control/Destinatario/listarDestinatario',
                                                            id: 'id_funcionario',
                                                            root: 'datos',
                                                            totalProperty: 'total',
                                                            fields: ['id_funcionario', 'desc_funcionario1'],
                                                            remoteSort: true,
                                                            autoLoad: true,
                                                            baseParams: { dir:'ASC',
                                                                sort:'id_aom',
                                                                limit:'100',
                                                                start:'0',
                                                                id_aom:maestro.id_aom,
                                                            }
                                                        }),
                                                        displayField: 'desc_funcionario1',
                                                        valueField: 'id_funcionario',
                                                    }]
                                            }]
                                        },
                                    ]
                                }),

                            ]
                        },
                            {
                                title: 'Resumen (4-R-27)',
                                layout: 'column',
                                defaults: {width: 600},
                                autoScroll: true,
                                defaultType: 'textfield',
                                items: [
                                    {
                                        xtype: 'htmleditor',
                                        name: 'resumen',
                                        width:600,
                                        height:459,
                                        id: this.idContenedor+'_resumen',
                                    },
                                ]
                            },
                            {
                                title: 'No Conformidades (4-R-10)',
                                layout: 'fit',
                                region:'center',
                                items: [
                                    noConformidad
                                ]
                            },
                            {
                                title: 'Recomendacion',
                                layout: 'column',
                                defaults: {width: 600},
                                autoScroll: true,
                                defaultType: 'textfield',
                                items: [
                                    {
                                        xtype: 'textarea',
                                        name: 'recomendacion',
                                        width:600,
                                        height:459,
                                        id: this.idContenedor+'_recomendacion',
                                    },
                                ]
                            }
                        ]
                    }]
                }],
                padding: this.paddingForm,
                bodyStyle: this.bodyStyleForm,
                border: this.borderForm,
                frame: this.frameForm,
                autoDestroy: true,
                autoScroll: true,
                region: 'center'
            });
            this.formularioVentana = new Ext.Window({
                width: 700,
                height: 700,
                modal: true,
                closeAction: 'hide',
                labelAlign: 'top',
                title: 'Informe Auditoria',
                bodyStyle: 'padding:5px',
                layout: 'border',
                items: [
                    this.form
                ],
                buttons: [{
                    text: 'Guardar',
                    handler: this.onSubmit,
                    scope: this
                }, {
                    text: 'Declinar',
                    handler: function() {
                        this.formularioVentana.hide();
                    },
                    scope: this
                }]
            });
        },
        onSubmit:function(){
            const arratFormulario = [];
            const submit={};
            Ext.each(this.form.getForm().items.keys, function(element, index){
                obj = Ext.getCmp(element);
                if(obj.items){
                    Ext.each(obj.items.items, function(elm, ind){
                        submit[elm.name]=elm.getValue();
                    },this)
                } else {
                    submit[obj.name]=obj.getValue();
                    if(obj.name == 'id_tnorma' || obj.name == 'id_tobjeto'){
                        if(obj.selectedIndex!=-1){
                            submit[obj.name]=obj.store.getAt(obj.selectedIndex).id;
                        }
                    }
                }
            },this);
            const { id_destinatario, id_destinatarios, recomendacion, resumen  } = submit;
            const v3g = { id_destinatario, id_destinatarios, recomendacion };
            arratFormulario.push(v3g);
            const maestro = this.sm.getSelected().data;

            if (this.form.getForm().isValid()) {
                Phx.CP.loadingShow();
                Ext.Ajax.request({
                    url: '../../sis_auditoria/control/AuditoriaOportunidadMejora/planifiacionAuditoria',
                    params: {
                        id_aom :maestro.id_aom,
                        arratFormulario: JSON.stringify(arratFormulario),
                        resumen : resumen,
                        informe :'si'
                    },
                    isUpload: false,
                    success: function(a,b,c){
                        this.store.rejectChanges();
                        Phx.CP.loadingHide();
                        this.formularioVentana.hide();
                        this.reload();
                    },
                    argument: this.argumentSave,
                    failure: this.conexionFailure,
                    timeout: this.timeout,
                    scope: this
                });
            } else {
                Ext.MessageBox.alert('Validación', 'Existen datos inválidos en el formulario. Corrija y vuelva a intentarlo');
            }
        },
        cargaFormulario: function(data){
            var obj,key;
            Ext.each(this.form.getForm().items.keys, function(element, index){
                obj = Ext.getCmp(element);
                if(obj&&obj.items){
                    Ext.each(obj.items.items, function(elm, b, c){
                        if(elm.getXType()=='combo'&&elm.mode=='remote'&&elm.store!=undefined){
                            if (!elm.store.getById(data[elm.name])) {
                                rec = new Ext.data.Record({[elm.displayField]: data[elm.gdisplayField], [elm.valueField]: data[elm.name] },data[elm.name]);
                                elm.store.add(rec);
                                elm.store.commitChanges();
                                elm.modificado = true;
                            }
                        }
                        elm.setValue(data[elm.name]);
                    },this);
                } else {
                    key = element.replace(this.idContenedor+'_','');
                    if(obj){
                        if((obj.getXType()=='combo'&&obj.mode=='remote'&&obj.store!=undefined)||key=='id_centro_costo'){
                            if (!obj.store.getById(data[key])) {
                                rec = new Ext.data.Record({[obj.displayField]: data[obj.gdisplayField], [obj.valueField]: data[key] },data[key]);
                                obj.store.add(rec);
                                obj.store.commitChanges();
                                obj.modificado = true;
                            }
                        }
                        obj.setValue(data[key]);
                    }
                }
            },this);
        },
        preparaMenu:function(n){
            var tb =this.tbar;
            Phx.vista.OportunidadMejoraInforme.superclass.preparaMenu.call(this,n);
            this.getBoton('sig_estado').enable();
            this.getBoton('btnChequeoDocumentosWf').enable();
            this.getBoton('diagrama_gantt').enable();
            this.getBoton('ant_estado').enable();
            return tb
        },
        liberaMenu:function(){
            var tb = Phx.vista.OportunidadMejoraInforme.superclass.liberaMenu.call(this);
            if(tb){
                this.getBoton('sig_estado').disable();
                this.getBoton('btnChequeoDocumentosWf').disable();
                this.getBoton('diagrama_gantt').disable();
                this.getBoton('ant_estado').disable();
            }
            return tb
        },
        onReporte:function () {
            var rec=this.sm.getSelected();
            Ext.Ajax.request({
                url:'../../sis_auditoria/control/AuditoriaOportunidadMejora/reporteResumen',
                params:{'id_aom':rec.data.id_aom},
                success: this.successExport,
                failure: this.conexionFailure,
                timeout:this.timeout,
                scope:this
            });
        },
        onRecomendacion:function () {
            var data = this.getSelectedData();
            if(data){
                this.cmpRecomendacion.setValue(data.recomendacion);
                this.ventanaRecomendacion.show();
            }
        },
        recomendacionForm:function () {
            var recomendacion = new Ext.form.TextArea({
                name: 'recomendacion',
                msgTarget: 'title',
                fieldLabel: 'Recomendacion',
                allowBlank: true,
                width:400,
                height:100
            });
            this.formRecomendacion = new Ext.form.FormPanel({
                baseCls: 'x-plain',
                autoDestroy: true,
                border: false,
                layout: 'form',
                autoHeight: true,
                items: [recomendacion]
            });
            this.ventanaRecomendacion = new Ext.Window({
                title: 'Recomendacion de Auditoria',
                collapsible: true,
                maximizable: true,
                autoDestroy: true,
                width: 550,
                height: 200,
                layout: 'fit',
                plain: true,
                bodyStyle: 'padding:5px;',
                buttonAlign: 'center',
                items: this.formRecomendacion,
                modal:true,
                closeAction: 'hide',
                buttons: [{
                    text: 'Guardar',
                    handler: this.saveRecomendacion,
                    scope: this},
                    {
                        text: 'Cancelar',
                        handler: function(){ this.ventanaRecomendacion.hide() },
                        scope: this
                    }]
            });
            this.cmpRecomendacion = this.formRecomendacion.getForm().findField('recomendacion');
        },
        saveRecomendacion:function () {
            var d = this.getSelectedData();
            Phx.CP.loadingShow();
            Ext.Ajax.request({
                url: '../../sis_auditoria/control/AuditoriaOportunidadMejora/insertSummary',
                params: {
                    id_aom: d.id_aom,
                    recomendacion: this.cmpRecomendacion.getValue()
                },
                success: this.successSincExtra,
                failure: this.conexionFailure,
                timeout: this.timeout,
                scope: this
            });
        },
        successSincExtra:function(resp){
            Phx.CP.loadingHide();
            var reg = Ext.util.JSON.decode(Ext.util.Format.trim(resp.responseText));
            if(!reg.ROOT.error){
                if(this.ventanaRecomendacion){
                    this.ventanaRecomendacion.hide();
                }
                this.load({params: {start: 0, limit: this.tam_pag}});
            }else{
                alert('ocurrio un error durante el proceso')
            }
        },
        onNoConformidades:function () {
            var rec = this.sm.getSelected();
            Phx.CP.loadWindows('../../../sis_auditoria/vista/no_conformidad/NoConformidadGestion.php', 'Gestion No conformidades',{
                //modal : true,
                width:'90%',
                height:'90%'
            }, rec.data,this.idContenedor, 'NoConformidadGestion');
        },
        onReloadPage : function(m){
            this.maestro = m;
            console.log('=22222>',this);
            this.store.baseParams = {
                id_gestion:  this.maestro.id_gestion,
                desde:  this.maestro.desde,
                hasta:  this.maestro.hasta,
                start:0,
                limit:50,
                sort:'id_aom',
                dir:'DESC',
                interfaz: this.nombreVista,
                contenedor: this.idContenedor
            };
            this.store.reload({ params: this.store.baseParams});
        },
        formularioNoConformidad:function(data){
            const maestro = this.sm.getSelected().data;

            const me = this;
            let id_modificacion = null;
            if(data){
                id_modificacion = data.id_nc
            }
            const isForm = new Ext.form.FormPanel({
                id: this.idContenedor + '_no_form',
                items: [new Ext.form.FieldSet({
                    // title:'Datos Generales',
                    collapsible: false,
                    border: true,
                    layout: 'form',
                    defaults: { width: 600},
                    items: [
                        {
                            xtype: 'field',
                            fieldLabel: 'Código Auditoria',
                            name: 'nro_tramite_wf',
                            anchor: '100%',
                            value: maestro.nro_tramite_wf,
                            readOnly :true,
                            style: 'background-image: none;'

                        },
                        {
                            xtype: 'field',
                            fieldLabel: 'Nombre Auditoria',
                            name: 'nombre_aom1',
                            anchor: '100%',
                            value: maestro.nombre_aom1,
                            readOnly :true,
                            style: 'background-image: none;'

                        },
                        {
                            xtype: 'combo',
                            name: 'id_parametro',
                            fieldLabel: 'Tipo',
                            allowBlank: false,
                            emptyText: 'Elija una opción...',
                            store: new Ext.data.JsonStore({
                                url: '../../sis_auditoria/control/Parametro/listarParametro',
                                id: 'id_parametro',
                                root: 'datos',
                                sortInfo: {
                                    field: 'valor_parametro',
                                    direction: 'ASC'
                                },
                                totalProperty: 'total',
                                fields: ['id_parametro', 'valor_parametro', 'id_tipo_parametro'],
                                remoteSort: true,
                                baseParams: {par_filtro: 'prm.id_parametro#prm.valor_parametro',tipo_no:'TIPO_NO_CONFORMIDAD'}
                            }),
                            valueField: 'id_parametro',
                            displayField: 'valor_parametro',
                            gdisplayField: 'valor_parametro',
                            hiddenName: 'id_parametro',
                            mode: 'remote',
                            triggerAction: 'all',
                            lazyRender: true,
                            pageSize: 15,
                            minChars: 2,
                            anchor: '100%',
                        },
                        {
                            xtype: 'combo',
                            name: 'id_uo',
                            fieldLabel: 'Area',
                            allowBlank: false,
                            resizable:true,
                            emptyText: 'Elija una opción...',
                            store: new Ext.data.JsonStore({
                                url: '../../sis_auditoria/control/AuditoriaOportunidadMejora/getListUO',
                                id: 'id_uo',
                                root: 'datos',
                                sortInfo: {
                                    field: 'nombre_unidad',
                                    direction: 'ASC'
                                },
                                totalProperty: 'total',
                                fields: ['id_uo', 'nombre_unidad','codigo','nivel_organizacional'],
                                remoteSort: true,
                                baseParams: {par_filtro: 'nombre_unidad'}
                            }),
                            valueField: 'id_uo', //modificado
                            displayField: 'nombre_unidad',
                            gdisplayField: 'nombre_unidad',
                            hiddenName: 'id_uo',
                            mode: 'remote',
                            triggerAction: 'all',
                            lazyRender: true,
                            pageSize: 15,
                            minChars: 2,
                            anchor: '100%',
                            readOnly :true,
                            style: 'background-image: none;'
                        },
                        {
                            xtype: 'combo',
                            name: 'id_funcionario',
                            fieldLabel: 'Resp. Area de NC',
                            allowBlank: false,
                            resizable:true,
                            emptyText: 'Elija una opción...',
                            store: new Ext.data.JsonStore({
                                url: '../../sis_auditoria/control/NoConformidad/listarSomUsuario',
                                id: 'id_funcionario',
                                root: 'datos',
                                sortInfo: {
                                    field: 'id_funcionario',
                                    direction: 'ASC'
                                },
                                totalProperty: 'total',
                                fields: ['id_funcionario', 'desc_funcionario1'],
                                remoteSort: true,
                                baseParams: {par_filtro: 'ofunc.id_funcionario#ofunc.desc_funcionario1'}
                            }),
                            valueField: 'id_funcionario',
                            displayField: 'desc_funcionario1',
                            gdisplayField: 'desc_funcionario1',
                            hiddenName: 'id_funcionario',
                            mode: 'remote',
                            triggerAction: 'all',
                            lazyRender: true,
                            pageSize: 15,
                            minChars: 2,
                            anchor: '100%',
                            readOnly :true,
                            style: 'background-image: none;'
                        },
                        new Ext.form.FieldSet({
                            collapsible: false,
                            layout:"column",
                            border : false,
                            defaults: {
                                flex: 1
                            },
                            items: [
                                new Ext.form.Label({
                                    text: 'Calidad :',
                                    style: 'margin: 5px'
                                }),
                                {
                                    xtype: 'checkbox',
                                    name : 'calidad',
                                    fieldLabel : 'Calidad',
                                    renderer : function(value, p, record) {
                                        return record.data['calidad'] == 'true' ? 'si' : 'no';
                                    },
                                    gwidth : 50
                                }, //
                                new Ext.form.Label({
                                    text: 'Medio Ambiente  :',
                                    style: 'margin: 5px'
                                }),
                                {
                                    xtype: 'checkbox',
                                    name : 'medio_ambiente',
                                    fieldLabel : 'Medio Ambiente',
                                    renderer : function(value, p, record) {
                                        return record.data['medio_ambiente'] == 'true' ? 'si' : 'no';
                                    },
                                    gwidth : 50
                                },
                                new Ext.form.Label({
                                    text: 'Seguridad :',
                                    style: 'margin: 5px'
                                }),
                                {
                                    xtype: 'checkbox',
                                    name : 'seguridad',
                                    fieldLabel : 'Seguridad',
                                    renderer : function(value, p, record) {
                                        return record.data['seguridad'] == 'true' ? 'si' : 'no';
                                    },
                                    gwidth : 50
                                }, //
                                new Ext.form.Label({
                                    text: 'Responsabilidad Social :',
                                    style: 'margin: 5px'
                                }),
                                {
                                    xtype: 'checkbox',
                                    name : 'responsabilidad_social',
                                    fieldLabel : 'Responsabilidad Social',
                                    renderer : function(value, p, record) {
                                        return record.data['responsabilidad_social'] == 'true' ? 'si' : 'no';
                                    },
                                    gwidth : 50
                                },
                                new Ext.form.Label({
                                    text: 'Sistemas Integrados :',
                                    style: 'margin: 5px'
                                }),
                                {
                                    xtype: 'checkbox',
                                    name : 'sistemas_integrados',
                                    fieldLabel : 'Sistemas Integrados',
                                    renderer : function(value, p, record) {
                                        return record.data['sistemas_integrados'] == 'true' ? 'si' : 'no';
                                    },
                                    gwidth : 50
                                }
                            ]
                        }),
                        {
                            xtype: 'textarea',
                            name: 'descrip_nc',
                            fieldLabel: 'Descripcion',
                            allowBlank: true,
                            anchor: '100%',
                            gwidth: 280
                        },
                        {
                            xtype: 'textarea',
                            name: 'evidencia',
                            fieldLabel: 'Evidencia',
                            allowBlank: true,
                            anchor: '100%',
                            gwidth: 150
                        },
                        {
                            xtype: 'textarea',
                            name: 'obs_resp_area',
                            fieldLabel: 'Observacion responsable de Area',
                            allowBlank: true,
                            anchor: '100%',
                            gwidth: 150
                        },
                        {
                            xtype: 'textarea',
                            name: 'obs_consultor',
                            fieldLabel: 'Observacion Consultor',
                            allowBlank: true,
                            anchor: '100%',
                            gwidth: 150
                        },
                        {
                            anchor: '100%',
                            bodyStyle: 'padding:10px;',
                            title: 'Puntos de Norma',
                            region: 'center',
                            items:[
                                {
                                    xtype: 'combo',
                                    name: 'id_norma',
                                    fieldLabel: 'Norma',
                                    allowBlank: false,
                                    // id: this.idContenedor+'_id_norma',
                                    emptyText: 'Elija una opción...',
                                    store: new Ext.data.JsonStore({
                                        url: '../../sis_auditoria/control/Norma/listarNorma',
                                        id: 'id_norma',
                                        root: 'datos',
                                        sortInfo: {
                                            field: 'nombre_norma',
                                            direction: 'ASC'
                                        },
                                        totalProperty: 'total',
                                        fields: ['id_norma', 'id_tipo_norma','nombre_norma','sigla_norma','descrip_norma'],
                                        remoteSort: true,
                                        baseParams: {par_filtro: 'nor.sigla_norma'}
                                    }),
                                    valueField: 'id_norma',
                                    displayField: 'sigla_norma',
                                    gdisplayField: 'sigla_norma',
                                    tpl:'<tpl for="."><div class="x-combo-list-item"><p style="color:#01010a">{sigla_norma} - {nombre_norma}</p></div></tpl>',
                                    hiddenName: 'id_norma',
                                    mode: 'remote',
                                    width: 680,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    pageSize: 15,
                                    minChars: 2
                                },
                                {
                                    xtype: 'itemselector',
                                    name: 'id_pn',
                                    fieldLabel: 'Punto Noma',
                                    imagePath: '../../../pxp/lib/ux/images/',
                                    drawUpIcon:false,
                                    drawDownIcon:false,
                                    drawTopIcon:false,
                                    drawBotIcon:false,
                                    multiselects: [{
                                        width: 330,
                                        height: 200,
                                        store: new Ext.data.JsonStore({
                                            url: '../../sis_auditoria/control/PuntoNorma/listarPuntoNormaMulti',
                                            id: 'id_pn',
                                            root: 'datos',
                                            sortInfo: {
                                                field: 'nombre_pn',
                                                direction: 'ASC'
                                            },
                                            totalProperty: 'total',
                                            fields: ['id_pn', 'nombre_pn',],
                                            remoteSort: true,
                                            baseParams: {dir:'ASC',sort:'id_aom',limit:'100',start:'0'}
                                        }),
                                        displayField: 'nombre_pn',
                                        valueField: 'id_pn',
                                    },{
                                        width: 330,
                                        height: 200,
                                        store: new Ext.data.JsonStore({
                                            url: '../../sis_auditoria/control/PnormaNoconformidad/listarPnormaNoconformidad',
                                            id: 'id_pnnc',
                                            root: 'datos',
                                            totalProperty: 'total',
                                            fields: ['id_aom','id_pnnc','id_nc','nombre_pn','id_norma','desc_norma','desc_pn','id_pn'],
                                            remoteSort: true,
                                            baseParams: {dir:'ASC',sort:'id_pnnc',limit:'100',start:'0'}
                                        }),
                                        displayField: 'nombre_pn',
                                        valueField: 'id_pn',
                                    }]
                                }]
                        }
                    ]
                })],
                padding: this.paddingForm,
                bodyStyle: this.bodyStyleForm,
                border: this.borderForm,
                frame: this.frameForm,
                autoScroll: false,
                autoDestroy: true,
                autoScroll: true,
                region: 'center'
            });
            if(data){
                isForm.getForm().items.items[5].setValue(this.onBool(data.calidad));
                isForm.getForm().items.items[6].setValue(this.onBool(data.medio_ambiente));
                isForm.getForm().items.items[7].setValue(this.onBool(data.seguridad));
                isForm.getForm().items.items[8].setValue(this.onBool(data.responsabilidad_social));
                isForm.getForm().items.items[9].setValue(this.onBool(data.sistemas_integrados));

                isForm.getForm().items.items[10].setValue(data.descrip_nc);
                isForm.getForm().items.items[11].setValue(data.evidencia);
                isForm.getForm().items.items[12].setValue(data.obs_resp_area);
                isForm.getForm().items.items[13].setValue(data.obs_consultor);
                Ext.Ajax.request({
                    url:'../../sis_auditoria/control/NoConformidad/getNoConformidad',
                    params:{ id_nc: data.id_nc },
                    success:function(resp){
                        const reg = Ext.util.JSON.decode(Ext.util.Format.trim(resp.responseText));
                        console.log(reg.ROOT)
                        isForm.getForm().items.items[2].setValue(reg.ROOT.datos.id_parametro);
                        isForm.getForm().items.items[2].setRawValue(reg.ROOT.datos.valor_parametro);
                        isForm.getForm().items.items[14].setValue(reg.ROOT.datos.id_norma);
                        isForm.getForm().items.items[14].setRawValue(reg.ROOT.datos.sigla_norma);

                        isForm.getForm().items.items[15].multiselects[0].store.baseParams = {
                            dir: "ASC",
                            sort: "id_aom",
                            limit: "100",
                            start: "0",
                            id_norma: reg.ROOT.datos.id_norma,
                            item :maestro.id_aom
                        };
                        isForm.getForm().items.items[15].multiselects[1].store.baseParams = {
                            dir: "ASC",
                            sort: "id_aom",
                            limit: "100",
                            start: "0" ,
                            id_aom: maestro.id_aom,
                            id_nc: reg.ROOT.datos.id_nc
                        };
                        isForm.getForm().items.items[15].multiselects[0].store.load();
                        isForm.getForm().items.items[15].multiselects[1].store.load();
                    },
                    failure: this.conexionFailure,
                    timeout:this.timeout,
                    scope:this
                });
            }else {
                isForm.getForm().items.items[14].on('select', function(combo, record, index){
                    isForm.getForm().items.items[15].multiselects[0].store.baseParams = {dir: "ASC", sort: "id_aom", limit: "100", start: "0", id_norma: record.data.id_norma,item :maestro.id_aom};
                    isForm.getForm().items.items[15].modificado = true;
                    isForm.getForm().items.items[15].reset();
                },this);
            }


            Ext.Ajax.request({
                url:'../../sis_auditoria/control/NoConformidad/getUo',
                params:{ id_uo: maestro.id_uo },
                success:function(resp){
                    var reg = Ext.util.JSON.decode(Ext.util.Format.trim(resp.responseText));
                    isForm.getForm().items.items[3].setValue(reg.ROOT.datos.id_uo);
                    isForm.getForm().items.items[3].setRawValue(reg.ROOT.datos.nombre_unidad);
                    isForm.getForm().items.items[4].setValue(reg.ROOT.datos.id_funcionario);
                    isForm.getForm().items.items[4].setRawValue(reg.ROOT.datos.desc_funcionario1);
                },
                failure: this.conexionFailure,
                timeout:this.timeout,
                scope:this
            });



            this.ventanaNoConformidad = new Ext.Window({
                width: 780,
                height: 700,
                modal: true,
                closeAction: 'hide',
                labelAlign: 'bottom',
                title: 'Cronograma de Actividades por Auditoria',
                bodyStyle: 'padding:5px',
                layout: 'border',
                items: [isForm],
                buttons: [{
                    text: 'Guardar',
                    handler: function () {
                        let submit={};
                        Ext.each(isForm.getForm().items.keys, function(element, index){
                            obj = Ext.getCmp(element);
                            if(obj.items){
                                Ext.each(obj.items.items, function(elm, ind){
                                    submit[elm.name]=elm.getValue();
                                },this)
                            } else {
                                submit[obj.name]=obj.getValue();
                            }
                        },this);
                        console.log(submit);
                        Phx.CP.loadingShow();
                        Ext.Ajax.request({
                            url: '../../sis_auditoria/control/NoConformidad/insertarItemNoConformidad',
                            params: {
                                id_aom  : maestro.id_aom,
                                nro_tramite_padre : maestro.nro_tramite_wf,
                                id_parametro : submit.id_parametro,
                                id_uo : submit.id_uo,
                                id_funcionario : submit.id_funcionario,
                                calidad : submit.calidad,
                                medio_ambiente : submit.medio_ambiente,
                                responsabilidad_social : submit.responsabilidad_social,
                                seguridad : submit.seguridad,
                                sistemas_integrados : submit.sistemas_integrados,
                                descrip_nc : submit.descrip_nc,
                                evidencia : submit.evidencia,
                                obs_resp_area : submit.obs_resp_area,
                                obs_consultor : submit.obs_consultor,
                                id_norma : submit.id_norma,
                                id_pn : submit.id_pn,
                                id_nc : id_modificacion
                            },
                            isUpload: false,
                            success: function(a,b,c){
                                Phx.CP.loadingHide();
                                me.ventanaNoConformidad.hide();
                                me.tienda.load();
                                // this.storeProceso.load();
                            },
                            argument: this.argumentSave,
                            failure: this.conexionFailure,
                            timeout: this.timeout,
                            scope: this
                        });
                    },
                    scope: this
                }, {
                    text: 'Declinar',
                    handler: function() {
                        me.ventanaNoConformidad.hide();
                    },
                    scope: this
                }]
            });
        },
        onBool:function(valor){
            if(valor === 't'){
                return true;
            }
            return  false;
        },
        tabsouth:[
            {
                url:'../../../sis_auditoria/vista/destinatario/Destinatario.php',
                title:'Destinatarios',
                height:'50%',
                cls:'Destinatario'
            },
            {
                url:'../../../sis_auditoria/vista/no_conformidad/NoConformidadGestion.php',
                title:'No Conformidad',
                height:'50%',
                cls:'NoConformidadGestion'
            }
        ]
    };
</script>
