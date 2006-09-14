/*< blank basic *******************************************************************/
fValidate.prototype.blank = function()
{
	if ( this.typeMismatch( 'text' ) ) return;
	if ( this.isBlank() )
	{
		this.throwError( [this.elem.fName] );
	}
};
/*/>*/
/*< number numbers *******************************************************************/
fValidate.prototype.number = function( type, lb, ub )
{
	if ( this.typeMismatch( 'text' ) ) return;
	var num  = ( type == 0 ) ? parseInt( this.elem.value, 10 ) : parseFloat( this.elem.value );
	lb       = parseInt(this.setArg( lb, 0 ));
	ub       = parseInt(this.setArg( ub, Number.infinity ));
	if ( lb > ub )
	{
		this.devError( [lb, ub, this.elem.name] );
		return;
	}
	var fail = Boolean( isNaN( num ) || num != this.elem.value );
	if ( !fail )
	{
		switch( true )
		{
			case ( lb != false && ub != false ) : fail = !Boolean( lb <= num && num <= ub ); break;
			case ( lb != false ) : fail = Boolean( num < lb ); break;
			case ( ub != false ) : fail = Boolean( num > ub ); break;
		}
	}
	if ( fail )
	{
		this.throwError( [this.elem.fName] );
		return;
	}
	this.elemPass = true;
};
/*/>*/
/*< numeric numbers *******************************************************************/
fValidate.prototype.numeric = function( len )
{
	if ( this.typeMismatch( 'text' ) ) return;
	len = this.setArg( len, '*' );
	var regex = new RegExp( ( len == '*' ) ? "^\\d+$" : "^\\d{" + parseInt( len, 10 ) + "}\\d*$" );
	if ( !regex.test( this.elem.value ) )
	{
		if ( len == "*" )
		{
			this.throwError( [this.elem.fName] );
		} else {
			this.throwError( [len, this.elem.fName], 1 );
		}
	}
};
/*/>*/
/*< ip web *******************************************************************/
fValidate.prototype.ip = function( portMin, portMax )
{
	if ( this.typeMismatch( 'text' ) ) return;
	portMin = this.setArg( portMin, 0 );
	portMax = this.setArg( portMax, 99999 );
	if ( !( /^\d{1,3}(\.\d{1,3}){3}(:\d+)?$/.test( this.elem.value ) ) )
	{
		this.throwError();
	}
	else
	{
		var part, i = 0, parts = this.elem.value.split( /[.:]/ );
		while ( part = parts[i++] )
		{
			if ( i == 5 ) // Check port
			{
				if ( part < portMin || part > portMax )
				{
					this.throwError( [part, portMin, portMax], 1 );
				}
			}
			else if ( part < 0 || part > 255 )
			{
				this.throwError();
			}
		}
	}
};
/*/>*/
/*< length basic *******************************************************************/
fValidate.prototype.length = function( len, maxLen )
{
	if ( this.typeMismatch( 'text' ) ) return;
	var vlen = this.elem.value.length;
	len		= Math.abs( len );
	maxLen	= Math.abs( this.setArg( maxLen, Number.infinity ) );
	if ( len > maxLen )
	{
		this.devError( [len, maxLen, this.elem.name] );
		return;
	}
	if ( len > parseInt( vlen, 10 ) )
	{
		this.throwError( [this.elem.fName, len] );
	}
	if ( vlen > maxLen )
	{
		this.throwError( [this.elem.fName, maxLen, vlen], 1 );
	}
};
/*/>*/
/*< equalto logical *******************************************************************/
fValidate.prototype.equalto = function( oName )
{
	if ( this.typeMismatch( 'text' ) ) return;
	if ( typeof oName == 'undefined' )
	{
		this.paramError( 'oName' );
	}
	var otherElem = this.form.elements[oName];
	if ( this.elem.value != otherElem.value )
	{
		this.throwError( [this.elem.fName,otherElem.fName] );
	}
};
/*/>*/
/*< select controls *******************************************************************/
fValidate.prototype.select = function()
{
	if ( this.typeMismatch( 's1' ) ) return;
	if ( this.elem.selectedIndex == 0 )
	{
		this.throwError( [this.elem.fName] );
	}
};
/*/>*/
/*< selectm controls *******************************************************************/
fValidate.prototype.selectm = function( minS, maxS )
{
	if ( this.typeMismatch( 'sm' ) ) return;
	if ( typeof minS == 'undefined' )
	{
		this.paramError( 'minS' );
	}
	if ( maxS == 999 || maxS == '*' || typeof maxS == 'undefined' || maxS > this.elem.length ) maxS = this.elem.length;

	var count = 0;
	for ( var opt, i = 0; ( opt = this.elem.options[i] ); i++ )
	{
		if ( opt.selected ) count++;
	}

	if ( count < minS || count > maxS )
	{
		this.throwError( [minS, maxS, this.elem.fName, count] );
	}
};
/*/>*/
/*< email web *******************************************************************/
fValidate.prototype.email = function( level )
{
	if ( this.typeMismatch( 'text' ) ) return;
	if ( typeof level == 'undefined' ) level = 0;
	var emailPatterns = [
		/.+@.+\..+$/i,
		/^\w.+@\w.+\.[a-z]+$/i,
		/^\w[-_a-z~.]+@\w[-_a-z~.]+\.[a-z]{2}[a-z]*$/i,
		/^\w[\w\d]+(\.[\w\d]+)*@\w[\w\d]+(\.[\w\d]+)*\.[a-z]{2,7}$/i
		];
	if ( ! emailPatterns[level].test( this.elem.value ) )
	{
		this.throwError();
	}
};
/*/>*/
/*< eitheror logical *******************************************************************/
fValidate.prototype.eitheror = function()
{
	if ( this.typeMismatch( 'hidden' ) ) return;
	if ( typeof arguments[0] == 'undefined' )
	{
		this.paramError( 'delim' );
		return;
	}
	if ( typeof arguments[1] == 'undefined' )
	{
		this.paramError( 'fields' );
		return;
	}
	var arg, i  = 0,
		fields  = new Array(),
		field,
		nbCount = 0,
		args    = arguments[1].split( arguments[0] );

	this.elem.fields = new Array();

	while ( arg = args[i++] )
	{
		field = this.form.elements[arg];
		fields.push( field.fName );
		this.elem.fields.push( field );

		if ( !this.isBlank( arg ) )
		{
			nbCount++;
		}
	}
	if ( nbCount != 1 )
	{
		this.throwError( [fields.join( "\n\t-" )] );
	}
};
/*/>*/
/*< allornone logical *******************************************************************/
fValidate.prototype.allornone = function()
{
	if ( this.typeMismatch( 'hidden' ) ) return;
	if ( typeof arguments[0] == 'undefined' )
	{
		this.paramError( 'delim' );
		return;
	}
	if ( typeof arguments[1] == 'undefined' )
	{
		this.paramError( 'fields' );
		return;
	}
	var arg, i  = 0,
		fields  = new Array(),
		field,
		nbCount = 0,
		args    = arguments[1].split( arguments[0] );

	this.elem.fields = new Array();

	while ( arg = args[i++] )
	{
		field = this.form.elements[arg];
		fields.push( field.fName );
		this.elem.fields.push( field );

		if ( !this.isBlank( arg ) )
		{
			nbCount++;
		}
	}
	if ( nbCount > 0 && nbCount < args.length )
	{
		this.throwError( [fields.join( "\n\t-" ), nbCount] );
	}
};
/*/>*/
/*< file controls *******************************************************************/
fValidate.prototype.file = function( extensions, cSens )
{
	if ( this.typeMismatch( 'file' ) ) return;
	if ( typeof extensions == 'undefined' )
	{
		this.paramError( 'extensions' );
		return;
	}
	cSens = Boolean( cSens ) ? "" : "i";
	var regex = new RegExp( "^.+\\.(" + extensions.replace( /,/g, "|" ) + ")$", cSens );
	if ( ! regex.test( this.elem.value ) )
	{
		this.throwError( [extensions.replace( /,/g, "\n" )] );
	}
};
/*/>*/
/*< custom special *******************************************************************/
fValidate.prototype.custom = function( reverseTest )
{
	if ( this.typeMismatch( 'text' ) ) return;
	flags     = ( flags ) ? flags.replace( /[^gim]/ig ) : "";
	var regex = new RegExp( this.elem.getAttribute( this.config.pattern ), flags );
	if ( !regex.test( this.elem.value ) )
	{
		this.throwError( [this.elem.fName] );
	}
};
/*/>*/
/*< checkbselect logical *******************************************************************/
fValidate.prototype.checkbselect = function()
{
	if ( this.typeMismatch( 'hidden' ) ) return;
	if ( typeof arguments[0] == 'undefined' )
	{
		this.paramError( 'delim' );
		return;
	}
	if ( typeof arguments[1] == 'undefined' )
	{
		this.paramError( 'fields' );
		return;
	}
	if ( typeof arguments[2] == 'undefined' )
	{
		this.paramError( 'fields' );
		return;
	}
	var arg, i  = 0,
		fields  = new Array(),
		field,
		nbCount = 0,
		args    = arguments[2].split( arguments[0] );

	this.elem.fields = new Array();

	this.form.elements[arguments[1]].checked ? checkb=1 : checkb=0;
	while ( arg = args[i++] )
	{
		field = this.form.elements[arg];
		fields.push( field.name );
		this.elem.fields.push( field );

		 field.selectedIndex != 0 ? select=1 : select=0;
	}

	if ( !checkb && !select)
	{
		this.throwError( [fields.join( "\n\t-" ), nbCount] );
	}
};
/*/>*/
/*	EOF */