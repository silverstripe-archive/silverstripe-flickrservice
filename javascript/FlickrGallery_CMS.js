Behaviour.register({			
	'#Form_EditForm_Method' : {
		initialise : function() {
			this.toggleFields();
		},
		onchange : function() {
				this.toggleFields();
		},
		
		toggleFields : function() {
				//hide all fields
				var widgetFields = $$('#Root_Content_set_Photos div');
				for(i=1; i < 4; i++){
					Element.hide(widgetFields[i]);
				}
				
				switch(this.value){
					case '1':
						Element.toggle(widgetFields[1]);
						Element.toggle(widgetFields[2]);
						break;
					case '2':
						Element.toggle(widgetFields[2]);
						break;
					case '3':
						Element.toggle(widgetFields[1]);
						Element.toggle(widgetFields[3]);
						Element.disable(widgetFields[5])
						break;
				
				}
			}
	
		}
	});


