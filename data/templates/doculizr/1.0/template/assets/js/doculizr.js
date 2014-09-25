function hideInheritedMembers(obj, tableId) {
    obj.modeHideInherited = !(obj.modeHideInherited || false);
    console.log("Whether to hide", obj.modeHideInherited);
    var elements = document.querySelectorAll(tableId+' tr');
    for(var id=0; id<elements.length; id++) {
        if(elements[id].className.match(/inherited/g) && obj.modeHideInherited) {
            console.log("hidding");
            elements[id].className = elements[id].className + ' inherit-invisible';
        }else{
            console.log("showing");
            elements[id].className = elements[id].className.replace('inherit-invisible', '');
        }
    }
    obj.innerHTML = (obj.modeHideInherited) ? 'Show inherited methods' : 'Hide inherited methods';
    return false;
}