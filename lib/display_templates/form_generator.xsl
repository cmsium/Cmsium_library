<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="header">
        <h1>
            <xsl:apply-templates select="@*|node()"/>
        </h1>
    </xsl:template>

    <xsl:template match="text">
        <p>
            <xsl:apply-templates select="@*|node()"/>
        </p>
    </xsl:template>

    <xsl:template match="field">
        <input>
            <xsl:attribute name="onfocusout">validate_<xsl:value-of select="./@validate" />()</xsl:attribute>
            <xsl:apply-templates select="@*[name()!='validate']|node()"/>
        </input>
    </xsl:template>

    <xsl:template match="hidden_field">
        <input type="hidden">
            <xsl:apply-templates select="@*|node()"/>
        </input>
    </xsl:template>

    <xsl:template match="action">
        <xsl:for-each select="./*">
            <label>
                <input type="submit">
                    <xsl:attribute name="value">
                        <xsl:value-of select="." />
                    </xsl:attribute>
                    <xsl:apply-templates select="@*"/>
                </input>
            </label>
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>