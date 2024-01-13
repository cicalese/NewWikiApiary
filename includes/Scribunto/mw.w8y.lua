-- Variable instantiation
local w8y = {}
local php

function w8y.setupInterface()
    -- Interface setup
    w8y.setupInterface = nil
    php = mw_interface
    mw_interface = nil

    -- Register library within the "mw.slots" namespace
    mw = mw or {}
    mw.w8y = w8y

    package.loaded['mw.w8y'] = w8y
end

-- w8y function
function w8y.w8y( action, id )

    return php.w8y( action, id )
end

return w8y
