function toggleInheritedMembers(obj, tableId) {
    obj.modeShowInherited = !obj.modeShowInherited;
    var elements = document.querySelectorAll(tableId+' tr');
    for(var id=0; id<elements.length; id++) {
        if(elements[id].className.match(/inherited/g) && !obj.modeShowInherited) {
            elements[id].className = elements[id].className + ' inherit-invisible';
        }else{
            elements[id].className = elements[id].className.replace('inherit-invisible', '');
        }
    }
    obj.innerHTML = (obj.modeShowInherited) ? 'Hide inherited members' : 'Show inherited members';
    return false;
}